<?php

namespace Cthulhu\ast\tokens;

use Cthulhu\loc\Span;

class FloatToken extends LiteralToken {
  public int $precision;

  public function __construct(Span $span, string $lexeme, int $precision) {
    parent::__construct($span, $lexeme);
    $this->precision = $precision;
  }

  public function __debugInfo() {
    return [
      'type' => 'Float',
      'lexeme' => $this->lexeme,
    ];
  }
}
