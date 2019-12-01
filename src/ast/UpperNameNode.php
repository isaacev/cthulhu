<?php

namespace Cthulhu\ast;

use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;
use Cthulhu\Source;

class UpperNameNode extends Node {
  public string $ident;

  function __construct(Source\Span $span, string $value) {
    assert($value[0] >= 'A' && $value[0] <= 'Z');
    parent::__construct($span);
    $this->ident = $value;
  }

  static function from_token(Token $token): self {
    assert($token->type === TokenType::UPPER_NAME);
    return new self($token->span, $token->lexeme);
  }
}
