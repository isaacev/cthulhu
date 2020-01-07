<?php

namespace Cthulhu\ast;

use Cthulhu\loc\Span;

class FloatToken extends LiteralToken {
  public int $precision;

  public function __construct(Span $span, string $lexeme, int $precision) {
    parent::__construct($span, $lexeme);
    $this->precision = $precision;
  }
}
