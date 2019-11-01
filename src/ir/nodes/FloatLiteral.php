<?php

namespace Cthulhu\ir\nodes;

class FloatLiteral extends Literal {
  public $value;
  public $precision;

  function __construct(float $value, int $precision) {
    parent::__construct();
    $this->value = $value;
    $this->precision = $precision;
  }

  function children(): array {
    return [];
  }
}
