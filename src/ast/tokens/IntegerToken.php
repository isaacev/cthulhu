<?php

namespace Cthulhu\ast\tokens;

class IntegerToken extends LiteralToken {
  public function __debugInfo() {
    return [
      'type' => 'Integer',
      'lexeme' => $this->lexeme,
    ];
  }
}
