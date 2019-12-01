<?php

namespace Cthulhu\ir\nodes;

class FloatConstPattern extends ConstPattern {
  public float $value;

  function __construct(float $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  function __toString(): string {
    return "$this->literal"; // TODO: floating point precision?
  }
}
