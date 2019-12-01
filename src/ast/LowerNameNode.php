<?php

namespace Cthulhu\ast;

use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;
use Cthulhu\Source;

class LowerNameNode extends Node {
  public string $ident;

  function __construct(Source\Span $span, string $value) {
    assert($value[0] >= 'a' && $value[0] <= 'z');
    parent::__construct($span);
    $this->ident = $value;
  }

  static function from_token(Token $token): self {
    assert($token->type === TokenType::LOWER_NAME);
    return new self($token->span, $token->lexeme);
  }
}
