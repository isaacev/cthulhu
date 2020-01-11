<?php

namespace Cthulhu\ast\tokens;

class StringToken extends LiteralToken {
  public function __debugInfo() {
    return [
      'type' => 'String',
      'lexeme' => $this->lexeme,
    ];
  }
}
