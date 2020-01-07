<?php

namespace Cthulhu\ast;

class IdentToken extends Token {
  public function is_lowercase(): bool {
    $first = $this->lexeme[0];
    return 'a' <= $first && $first <= 'z';
  }

  public function is_uppercase(): bool {
    return !$this->is_lowercase();
  }
}
