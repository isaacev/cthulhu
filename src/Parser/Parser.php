<?php

namespace Cthulhu\Parser;

use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;
use Cthulhu\Parser\Errors;

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
        return new AST\NameAnnotation($this->require_next_token(TokenType::IDENT)->lexeme);
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
      default:
        return Precedence::LOWEST;
    }
  }

  private function parse_expr_group(Token $left_paren): AST\Expression {
    $expr = $this->parse_expr();
    $this->require_next_token(TokenType::PAREN_RIGHT);
    return $expr;
  }

  private function parse_if_expr(Token $if_keyword): AST\IfExpression {
    $condition = $this->parse_expr();
    $if_left_brace = $this->require_next_token(TokenType::BRACE_LEFT);
    $if_clause = $this->parse_stmts(TokenType::BRACE_RIGHT);
    $if_right_brace = $this->require_next_token(TokenType::BRACE_RIGHT);

    $peek = $this->lexer->peek();
    if ($peek !== null && $peek->type === TokenType::KEYWORD_ELSE) {
      $else_keyword = $this->require_next_token(TokenType::KEYWORD_ELSE);
      $else_left_brace = $this->require_next_token(TokenType::BRACE_LEFT);
      $else_clause = $this->parse_stmts(TokenType::BRACE_RIGHT);
      $else_right_brace = $this->require_next_token(TokenType::BRACE_RIGHT);
    } else {
      $else_clause = null;
    }

    return new AST\IfExpression($condition, $if_clause, $else_clause);
  }

  private function parse_fn_expr(Token $fn_keyword): AST\FnExpression {
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
    $body = $this->parse_stmts(TokenType::BRACE_RIGHT);
    $right_brace = $this->require_next_token(TokenType::BRACE_RIGHT);
    return new AST\FnExpression($params, $return_note, $body);
  }

  private function parse_prefix_expr(): AST\Expression {
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
      case TokenType::IDENT:
        return new AST\Identifier($next->lexeme);
      default:
        throw new Errors\UnexpectedToken($next);
    }
  }

  private function parse_call_expr(AST\Expression $callee, Token $paren_left): AST\CallExpression {
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
    return new AST\CallExpression($callee, $args);
  }

  private function parse_postfix_expr(AST\Expression $left, Token $next): AST\Expression {
    switch ($next->type) {
      case TokenType::PLUS:
      case TokenType::DASH:
      case TokenType::STAR:
      case TokenType::SLASH:
        $right = $this->parse_expr($this->infix_token_precedence($next));
        return new AST\BinaryOperator($next->type, $left, $right);
      case TokenType::PAREN_LEFT:
        return $this->parse_call_expr($left, $next);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception("binary operator disagreement: $next->type");
        // @codeCoverageIgnoreEnd
    }
  }

  public function parse_expr(int $threshold = Precedence::LOWEST): AST\Expression {
    $left = $this->parse_prefix_expr();

    while ($threshold < $this->infix_token_precedence($this->lexer->peek())) {
      $left = $this->parse_postfix_expr($left, $this->lexer->next());
    }

    return $left;
  }

  private function parse_let_stmt(): AST\LetStatement {
    $let_keyword = $this->require_next_token(TokenType::KEYWORD_LET);
    $name = $this->require_next_token(TokenType::IDENT)->lexeme;
    $equals = $this->require_next_token(TokenType::EQUALS);
    $expr = $this->parse_expr();
    $semicolon = $this->require_next_token(TokenType::SEMICOLON);
    return new AST\LetStatement($name, $expr);
  }

  private function parse_expr_stmt(): AST\ExpressionStatement {
    $expr = $this->parse_expr();
    $semicolon = $this->require_next_token(TokenType::SEMICOLON);
    return new AST\ExpressionStatement($expr);
  }

  public function parse_stmt(): AST\Statement {
    switch ($this->lexer->peek()->type) {
      case TokenType::KEYWORD_LET:
        return $this->parse_let_stmt();
      default:
        return $this->parse_expr_stmt();
    }
  }

  private function parse_stmts(?string $terminal = null): AST\Block {
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

    return new AST\Block($stmts);
  }

  public function parse(): AST\Root {
    return new AST\Root($this->parse_stmts());
  }

  public static function from_string(string $text): Parser {
    return new Parser(Lexer::from_string($text));
  }
}
