<?php

namespace Cthulhu\Parser;

use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Parser\Lexer\Point;
use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;
use Cthulhu\Parser\Errors;
use Cthulhu\AST;

class Parser {
  public static function from_string(string $text): self {
    return new self(Lexer::from_string($text));
  }

  private $lexer;

  function __construct(Lexer $lexer) {
    $this->lexer = $lexer;
  }

  public function file(): AST\File {
    return new AST\File($this->items(false));
  }

  /**
   * ITEMS
   *
   * Items are nodes that can only exist at the top-level of a file or as
   * children of a `mod { ... }` node. Items can import a module into the
   * current namespace, declare a function, or declare a submodule.
   */

  private function items(bool $brace_wrapped): array {
    $items = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek->type === TokenType::EOF) {
        break;
      } else if ($brace_wrapped && $peek->type === TokenType::BRACE_RIGHT) {
        break;
      }
      $items[] = $this->item();
    }
    return $items;
  }

  private function item(): AST\Item {
    switch ($this->lexer->peek()->type) {
      case TokenType::KEYWORD_USE:
        return $this->use_item();
      case TokenType::KEYWORD_MOD:
        return $this->mod_item();
      case TokenType::KEYWORD_FN:
        return $this->fn_item();
      default:
        throw Errors::expected_item($this->lexer->text(), $this->lexer->next());
    }
  }

  private function use_item(): AST\UseItem {
    $keyword = $this->next(TokenType::KEYWORD_USE);
    $name = AST\IdentNode::from_token($this->next(TokenType::IDENT));
    $semi = $this->next(TokenType::SEMICOLON);
    $span = $keyword->span->extended_to($semi->span);
    return new AST\UseItem($span, $name);
  }

  private function mod_item(): AST\ModItem {
    // TODO
  }

  private function fn_item(): AST\FnItem {
    $keyword = $this->next(TokenType::KEYWORD_FN);
    $name = AST\IdentNode::from_token($this->next(TokenType::IDENT));
    $this->next(TokenType::PAREN_LEFT);
    $params = []; // TODO
    $this->next(TokenType::PAREN_RIGHT);
    $this->next(TokenType::THIN_ARROW);
    $returns = $this->type_annotation();
    $body = $this->block();
    $span = $keyword->span->extended_to($body->span);
    return new AST\FnItem($span, $name, $params, $returns, $body);
  }

  /**
   * STATEMENTS
   *
   * Statements can only exist as the children of a `Block` node. Block nodes
   * are children of either a function declaration or an if/else conditional.
   */

  private function block(): AST\BlockNode {
    $left = $this->next(TokenType::BRACE_LEFT);
    $stmts = $this->stmts();
    $right = $this->next(TokenType::BRACE_RIGHT);
    $span = $left->span->extended_to($right->span);
    return new AST\BlockNode($span, $stmts);
  }

  private function stmts(): array {
    $stmts = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek->type === TokenType::BRACE_RIGHT) {
        break;
      }
      $stmts[] = $this->stmt();
    }
    return $stmts;
  }

  private function stmt(): AST\Stmt {
    switch ($this->lexer->peek()->type) {
      case TokenType::KEYWORD_LET:
        return $this->let_stmt();
      default:
        return $this->expr_stmt();
    }
  }

  private function let_stmt(): AST\LetStmt {
    $keyword = $this->next(TokenType::KEYWORD_LET);
    $name = AST\IdentNode::from_token($this->next(TokenType::IDENT));
    $this->next(TokenType::EQUALS);
    $expr = $this->expr();
    $semi = $this->next(TokenType::SEMICOLON);
    $span = $keyword->span->extended_to($semi->span);
    return new AST\LetStmt($span, $name, $expr);
  }

  private function expr_stmt(): AST\ExprStmt {
    $expr = $this->expr();
    $semi = $this->next(TokenType::SEMICOLON);
    $span = $expr->span->extended_to($semi->span);
    return new AST\ExprStmt($span, $expr);
  }

  /**
   * EXPRESSIONS
   *
   * Expressions always produce a value and return it to the parent node.
   */

  private function expr(int $threshold = Precedence::LOWEST): AST\Expr {
    $left = $this->prefix_expr();
    while ($threshold < $this->infix_token_precedence($this->lexer->peek())) {
      $left = $this->postfix_expr($left, $this->lexer->next());
    }
    return $left;
  }

  private function prefix_expr(): AST\Expr {
    $next = $this->lexer->next();
    switch ($next->type) {
      case TokenType::IDENT:
        return $this->path_expr($next);
      case TokenType::LITERAL_STR:
        return $this->str_expr($next);
      case TokenType::LITERAL_NUM:
        return $this->num_expr($next);
      default:
        throw Errors::exepcted_expression($this->lexer->text(), $next);
    }
  }

  private function postfix_expr(AST\Expr $left, Token $next): AST\Expr {
    switch ($next->type) {
      case TokenType::PAREN_LEFT:
        return $this->call_expr($left, $next);
      default:
        // This condition *should* be unreachable unless there's a bug in the
        // parser where the `Parser::infix_token_precedence` method thinks a
        // token is a binary operator but this method doesn't recognize that
        // token as an operator.
        throw new \Exception('binary operator disagreement: ' . $next->type);
    }
  }

  private function call_expr(AST\Expr $callee, Token $paren_left): AST\CallExpr {
    $args = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type === TokenType::PAREN_RIGHT) {
        break;
      }
      $args[] = $this->expr();
      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type !== TokenType::COMMA) {
        break;
      } else {
        $this->next(TokenType::COMMA);
      }
    }
    $paren_right = $this->next(TokenType::PAREN_RIGHT);
    $span = $callee->span->extended_to($paren_right->span);
    return new AST\CallExpr($span, $callee, $args);
  }

  private function path_expr(Token $ident): AST\PathExpr {
    $segments = [ AST\IdentNode::from_token($ident) ];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type !== TokenType::DOUBLE_COLON) {
        break;
      }
      $this->next(TokenType::DOUBLE_COLON);
      $ident = $this->next(TokenType::IDENT);
      $segments[] = AST\IdentNode::from_token($ident);
    }
    return new AST\PathExpr($segments);
  }

  private function str_expr(Token $str): AST\StrExpr {
    $value = substr($str->lexeme, 1, -1);
    return new AST\StrExpr($str->span, $value, $str->lexeme);
  }

  private function num_expr(Token $num): AST\NumExpr {
    $value = intval($num->lexeme, 10);
    return new AST\NumExpr($num->span, $value, $num->lexeme);
  }

  /**
   * MISCELLANEOUS
   *
   * Utility methods for digesting tokens or parsing subtrees
   */

  private function next(string $type): Token {
    $next = $this->lexer->next();
    if ($next->type !== $type) {
      throw Errors::expected_token($this->lexer->text(), $next, $type);
    } else {
      return $next;
    }
  }

  private function infix_token_precedence(?Token $token): int {
    if ($token === null) {
      return Precedence::LOWEST;
    }

    switch ($token->type) {
      case TokenType::PLUS:
      case TokenType::DASH:
        return Precedence::SUM;
      case TokenType::STAR:
      case TokenType::SLASH:
        return Precedence::PRODUCT;
      case TokenType::PAREN_LEFT:
        return Precedence::ACCESS;
      case TokenType::LESS_THAN:
      case TokenType::LESS_THAN_EQ:
      case TokenType::GREATER_THAN:
      case TokenType::GREATER_THAN_EQ:
        return Precedence::RELATION;
      case TokenType::DOUBLE_COLON:
        return Precedence::ACCESS;
      default:
        return Precedence::LOWEST;
    }
  }

  private function type_annotation(): AST\Annotation {
    $peek = $this->lexer->peek();
    switch ($peek->type) {
      case TokenType::IDENT:
        $ident = $this->next(TokenType::IDENT);
        return new AST\NamedAnnotation($ident->span, $ident->lexeme);
      default:
        throw Errors::expected_annotation($this->lexer->text(), $peek);
    }
  }
}
