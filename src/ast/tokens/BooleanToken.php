<?php

namespace Cthulhu\ast\tokens;

class BooleanToken extends LiteralToken {
  public function __debugInfo() {
    return [
      'type' => 'Boolean',
      'lexeme' => $this->lexeme,
    ];
  }
}
