<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;

class LowerNameNode extends Node {
  public string $ident;

  public function __construct(Span $span, string $value) {
    assert($value[0] >= 'a' && $value[0] <= 'z');
    parent::__construct($span);
    $this->ident = $value;
  }

  public static function from_token(Token $token): self {
    assert($token->type === TokenType::LOWER_NAME);
    return new self($token->span, $token->lexeme);
  }
}
