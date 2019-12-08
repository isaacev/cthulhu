<?php

namespace Cthulhu\ir\patterns;

class FloatPattern extends Pattern {
  public float $value;

  function __construct(float $value) {
    $this->value = $value;
  }

  function __toString(): string {
    return (string)$this->value; // TODO: floating point precision?
  }
}
