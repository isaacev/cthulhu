<?php

namespace Cthulhu\ast;

class DelimToken extends Token {
  public function is_left(): bool {
    switch ($this->lexeme) {
      case '(':
      case '[':
      case '{':
        return true;
      default:
        return false;
    }
  }

  public function get_right(): string {
    switch ($this->lexeme) {
      case '(':
        return ')';
      case '[':
        return ']';
      case '{':
        return '}';
      default:
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  public static function from_char(Char $char): self {
    return new self($char->point->to_span(), $char->raw_char);
  }
}
