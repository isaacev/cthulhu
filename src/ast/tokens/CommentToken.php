<?php

namespace Cthulhu\ast\tokens;

class CommentToken extends Token {
  public function __debugInfo() {
    return [
      'type' => 'Comment',
      'lexeme' => $this->lexeme,
    ];
  }
}
