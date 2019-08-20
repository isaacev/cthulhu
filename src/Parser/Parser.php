<?php

namespace Cthulhu\Parser;

use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;
use Cthulhu\Parser\Errors;
use Cthulhu\AST;

class Parser {
  private $lexer;

  function __construct(Lexer $lexer) {
    $this->lexer = $lexer;
  }

  private function require_next_token(string $type): Token {
    $next = $this->lexer->next();
    if ($next === null) {
      throw new Errors\UnexpectedEndOfFile($type);
    } else if ($next->type !== $type) {
      throw new Errors\UnexpectedToken($next, $type);
    } else {
      return $next;
    }
  }

  private function parse_annotation(): AST\Annotation {
    $peek = $this->lexer->peek();
    if ($peek === null) {
      throw new Errors\UnexpectedEndOfFile();
    }

    switch ($peek->type) {
      case TokenType::IDENT:
        $ident = $this->require_next_token(TokenType::IDENT);
        return new AST\NamedAnnotation($ident->span, $ident->lexeme);
      default:
        throw new Errors\UnexpectedToken($peek);
    }
  }

  private function infix_token_precedence(?Token $tok): int {
    if ($tok === null) {
      return Precedence::LOWEST;
    }

    switch ($tok->type) {
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
      default:
        return Precedence::LOWEST;
    }
  }

  private function parse_expr_group(Token $left_paren): AST\Expr {
    $expr = $this->parse_expr();
    $this->require_next_token(TokenType::PAREN_RIGHT);
    return $expr;
  }

  private function parse_if_expr(Token $if_keyword): AST\IfExpr {
    $condition = $this->parse_expr();
    $if_left_brace = $this->require_next_token(TokenType::BRACE_LEFT);
    $if_stmts = $this->parse_stmts(TokenType::BRACE_RIGHT);
    $if_right_brace = $this->require_next_token(TokenType::BRACE_RIGHT);
    $if_block_span = $if_left_brace->span->extended_to($if_right_brace->span);
    $if_block = new AST\BlockNode($if_block_span, $if_stmts);

    $peek = $this->lexer->peek();
    if ($peek !== null && $peek->type === TokenType::KEYWORD_ELSE) {
      $else_keyword = $this->require_next_token(TokenType::KEYWORD_ELSE);
      $else_left_brace = $this->require_next_token(TokenType::BRACE_LEFT);
      $else_stmts = $this->parse_stmts(TokenType::BRACE_RIGHT);
      $else_right_brace = $this->require_next_token(TokenType::BRACE_RIGHT);
      $else_block_span = $else_left_brace->span->extended_to($else_right_brace->span);
      $else_block = new AST\BlockNode($else_block_span, $else_stmts);
    } else {
      $else_block = null;
    }

    $span = $if_keyword->span->extended_to(($else_block ? $else_right_brace : $if_right_brace)->span);
    return new AST\IfExpr($span, $condition, $if_block, $else_block);
  }

  private function parse_fn_expr(Token $fn_keyword): AST\FuncExpr {
    $left_paren = $this->require_next_token(TokenType::PAREN_LEFT);
    $params = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type !== TokenType::IDENT) {
        break;
      }

      $name = $this->require_next_token(TokenType::IDENT)->lexeme;
      $colon = $this->require_next_token(TokenType::COLON);
      $note = $this->parse_annotation();
      $params[] = [
        'name' => $name,
        'annotation' => $note,
      ];

      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type !== TokenType::COMMA) {
        break;
      } else {
        $this->require_next_token(TokenType::COMMA);
      }
    }

    $right_paren = $this->require_next_token(TokenType::PAREN_RIGHT);
    $colon = $this->require_next_token(TokenType::COLON);
    $return_note = $this->parse_annotation();
    $left_brace = $this->require_next_token(TokenType::BRACE_LEFT);
    $block_stmts = $this->parse_stmts(TokenType::BRACE_RIGHT);
    $right_brace = $this->require_next_token(TokenType::BRACE_RIGHT);
    $block_span = $left_brace->span->extended_to($right_brace->span);
    $block = new AST\BlockNode($block_span, $block_stmts);
    $span = $fn_keyword->span->extended_to($right_brace->span);
    return new AST\FuncExpr($span, $params, $return_note, $block);
  }

  private function parse_str_expr(Token $str_token): AST\StrExpr {
    $value = substr($str_token->lexeme, 1, -1);
    $span = $str_token->span;
    return new AST\StrExpr($span, $value, $str_token->lexeme);
  }

  private function parse_num_expr(Token $num_token): AST\NumExpr {
    $value = intval($num_token->lexeme, 10);
    $span = $num_token->span;
    return new AST\NumExpr($span, $value, $num_token->lexeme);
  }

  private function parse_prefix_expr(): AST\Expr {
    $next = $this->lexer->next();
    if ($next === null) {
      throw new Errors\UnexpectedEndOfFile();
    }

    switch ($next->type) {
      case TokenType::KEYWORD_IF:
        return $this->parse_if_expr($next);
      case TokenType::KEYWORD_FN:
        return $this->parse_fn_expr($next);
      case TokenType::PAREN_LEFT:
        return $this->parse_expr_group($next);
      case TokenType::LITERAL_STR:
        return $this->parse_str_expr($next);
      case TokenType::LITERAL_NUM:
        return $this->parse_num_expr($next);
      case TokenType::IDENT:
        return new AST\IdentExpr($next->span, $next->lexeme);
      default:
        throw new Errors\UnexpectedToken($next);
    }
  }

  private function parse_call_expr(AST\Expr $callee, Token $paren_left): AST\CallExpr {
    $args = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type === TokenType::PAREN_RIGHT) {
        break;
      }

      $args[] = $this->parse_expr();

      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type !== TokenType::COMMA) {
        break;
      } else {
        $this->require_next_token(TokenType::COMMA);
      }
    }

    $paren_right = $this->require_next_token(TokenType::PAREN_RIGHT);
    $span = $callee->span->extended_to($paren_right->span);
    return new AST\CallExpr($span, $callee, $args);
  }

  private function parse_postfix_expr(AST\Expr $left, Token $next): AST\Expr {
    switch ($next->type) {
      case TokenType::PLUS:
      case TokenType::DASH:
      case TokenType::STAR:
      case TokenType::SLASH:
      case TokenType::LESS_THAN:
      case TokenType::LESS_THAN_EQ:
      case TokenType::GREATER_THAN:
      case TokenType::GREATER_THAN_EQ:
        $right = $this->parse_expr($this->infix_token_precedence($next));
        $span = $left->span->extended_to($right->span);
        return new AST\BinaryExpr($span, $next->type, $left, $right);
      case TokenType::PAREN_LEFT:
        return $this->parse_call_expr($left, $next);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception("binary operator disagreement: $next->type");
        // @codeCoverageIgnoreEnd
    }
  }

  public function parse_expr(int $threshold = Precedence::LOWEST): AST\Expr {
    $left = $this->parse_prefix_expr();

    while ($threshold < $this->infix_token_precedence($this->lexer->peek())) {
      $left = $this->parse_postfix_expr($left, $this->lexer->next());
    }

    return $left;
  }

  private function parse_let_stmt(): AST\LetStmt {
    $let_keyword = $this->require_next_token(TokenType::KEYWORD_LET);
    $name = $this->require_next_token(TokenType::IDENT)->lexeme;
    $equals = $this->require_next_token(TokenType::EQUALS);
    $expr = $this->parse_expr();
    $semicolon = $this->require_next_token(TokenType::SEMICOLON);
    $span = $let_keyword->span->extended_to($semicolon->span);
    return new AST\LetStmt($span, $name, $expr);
  }

  private function parse_expr_stmt(): AST\ExprStmt {
    $expr = $this->parse_expr();
    $semicolon = $this->require_next_token(TokenType::SEMICOLON);
    $span = $expr->span->extended_to($semicolon->span);
    return new AST\ExprStmt($span, $expr);
  }

  public function parse_stmt(): AST\Stmt {
    switch ($this->lexer->peek()->type) {
      case TokenType::KEYWORD_LET:
        return $this->parse_let_stmt();
      default:
        return $this->parse_expr_stmt();
    }
  }

  private function parse_stmts(?string $terminal = null): array {
    $stmts = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek == null) {
        break;
      }

      if ($terminal !== null && $peek->type === $terminal) {
        break;
      }

      $stmts[] = $this->parse_stmt();
    }

    return $stmts;
  }

  public function parse(): AST\Root {
    return new AST\Root($this->parse_stmts());
  }

  public static function from_string(string $text): Parser {
    return new Parser(Lexer::from_string($text));
  }
}
