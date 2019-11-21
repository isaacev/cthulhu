<?php

namespace Cthulhu\ast;

use Cthulhu\Parser\Lexer\Token;

class StarSegment extends Node {
  public static function from_token(Token $token): StarSegment {
    return new StarSegment($token->span);
  }
}
