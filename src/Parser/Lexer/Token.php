<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Source;

class Token {
  public string $type;
  public Source\Span $span;
  public string $lexeme;

  function __construct(string $type, Source\Span $span, string $lexeme) {
    $this->type   = $type;
    $this->span   = $span;
    $this->lexeme = $lexeme;
  }

  public function description(): string {
    switch ($this->type) {
      case TokenType::ERROR:
        return 'unknown symbol';
      case TokenType::EOF:
        return 'the end of the file';
      case TokenType::LITERAL_INT:
        return 'integer literal';
      case TokenType::LITERAL_STR:
        return 'string literal';
      case TokenType::UPPER_NAME:
        return "name `$this->lexeme`";
      case TokenType::LOWER_NAME:
        return "name `$this->lexeme`";
      case TokenType::KEYWORD_LET:
        return 'keyword `let`';
      case TokenType::KEYWORD_IF:
        return 'keyword `if`';
      case TokenType::KEYWORD_ELSE:
        return 'keyword `else`';
      case TokenType::KEYWORD_FN:
        return 'keyword `fn`';
      case TokenType::KEYWORD_USE:
        return 'keyword `use`';
      case TokenType::KEYWORD_MOD:
        return 'keyword `mod`';
      default:
        return "`$this->type`";
    }
  }
}
