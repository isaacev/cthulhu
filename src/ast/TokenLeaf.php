<?php

namespace Cthulhu\ast;

use Cthulhu\ast\tokens\Token;

class TokenLeaf extends TokenTree {
  public int $offset;
  public Token $token;

  public function __construct(int $offset, Token $token) {
    $this->offset = $offset;
    $this->token  = $token;
  }
}
