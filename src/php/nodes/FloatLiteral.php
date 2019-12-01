<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FloatLiteral extends Literal {
  public float $value;
  public int $precision;

  function __construct(float $value, int $precision) {
    parent::__construct();
    $this->value = $value;
    $this->precision = $precision;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->float_literal($this->value, $this->precision);
  }
}
