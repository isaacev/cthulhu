<?php

namespace Cthulhu\ast\tokens;

use Cthulhu\ast\Char;

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

  public function balanced_with(self $other): bool {
    switch ($this->lexeme) {
      case '(':
        return $other->lexeme === ')';
      case ')':
        return $other->lexeme === '(';
      case '[':
        return $other->lexeme === ']';
      case ']':
        return $other->lexeme === '[';
      case '{':
        return $other->lexeme === '}';
      case '}':
        return $other->lexeme === '{';
      default:
        return false;
    }
  }

  public static function from_char(Char $char): self {
    return new self($char->point->to_span(), $char->raw_char);
  }

  public function __debugInfo() {
    return [
      'type' => 'Delim',
      'lexeme' => $this->lexeme,
      'is_left' => $this->is_left(),
    ];
  }
}
