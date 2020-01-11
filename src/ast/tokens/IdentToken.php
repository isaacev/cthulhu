<?php

namespace Cthulhu\ast\tokens;

class IdentToken extends Token {
  public function is_lowercase(): bool {
    $first = $this->lexeme[0];
    return 'a' <= $first && $first <= 'z';
  }

  public function is_uppercase(): bool {
    return !$this->is_lowercase();
  }

  public function __debugInfo() {
    return [
      'type' => 'Ident',
      'lexeme' => $this->lexeme,
      'is_lowercase' => $this->is_lowercase(),
    ];
  }
}
