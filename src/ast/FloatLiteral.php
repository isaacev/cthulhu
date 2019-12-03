<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FloatLiteral extends Literal {
  public float $value;
  public int $precision;
  public string $raw;

  function __construct(Source\Span $span, float $value, int $precision, string $raw) {
    parent::__construct($span);
    $this->value     = $value;
    $this->precision = $precision;
    $this->raw       = $raw;
  }
}
