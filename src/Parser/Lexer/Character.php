<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Source;

class Character {
  public $char;
  public $point;

  function __construct(string $char, Source\Point $point) {
    $this->char = $char;
    $this->point = $point;
  }

  public function is_whitespace(): bool {
    switch ($this->char) {
      case " ":  return true;
      case "\t": return true;
      case "\n": return true;
      default:   return false;
    }
  }

  public function is(string $char): bool {
    return $this->char === $char;
  }

  public function is_eof(): bool {
    return $this->is('');
  }

  public function is_between(string $low, string $high): bool {
    return $low <= $this->char && $this->char <= $high && !$this->is_eof();
  }

  public function is_letter(): bool {
    return $this->is_between('a', 'z') || $this->is_between('A', 'Z');
  }

  public function is_digit(): bool {
    return $this->is_between('0', '9');
  }
}
