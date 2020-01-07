<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class FloatLiteral extends Literal {
  public float $value;
  public int $precision;
  public string $raw;

  public function __construct(Span $span, float $value, int $precision, string $raw) {
    parent::__construct($span);
    $this->value     = $value;
    $this->precision = $precision;
    $this->raw       = $raw;
  }
}
