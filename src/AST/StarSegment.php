<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Token;

class StarSegment extends Node {
  public static function from_token(Token $token): StarSegment {
    return new StarSegment($token->span);
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('StarSegment', $visitor_table)) {
      $visitor_table['StarSegment']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'StarSegment'
    ];
  }
}
