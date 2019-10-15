<?php

namespace Cthulhu\ast;

use Cthulhu\Source;
use Cthulhu\Parser\Lexer\Token;

class IdentNode extends Node {
  public static function from_token(Token $token): IdentNode {
    return new IdentNode($token->span, $token->lexeme);
  }

  public $ident;

  function __construct(Source\Span $span, string $ident) {
    parent::__construct($span);
    $this->ident = $ident;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('IdentNode', $visitor_table)) {
      $visitor_table['IdentNode']($this);
    }
  }

  public function __toString(): string {
    return $this->ident;
  }

  public function jsonSerialize() {
    return [
      'type' => 'IdentNode',
      'ident' => $this->ident
    ];
  }
}