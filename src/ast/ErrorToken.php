<?php

namespace Cthulhu\ast;

use Cthulhu\loc\Span;

class ErrorToken extends LiteralToken {
  public string $message;

  public function __construct(Span $span, string $lexeme, string $message) {
    parent::__construct($span, $lexeme);
    $this->message = $message;
  }
}
