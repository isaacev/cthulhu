<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\val\FloatValue;

class FloatPattern extends Pattern {
  public FloatValue $value;

  public function __construct(FloatValue $value) {
    $this->value = $value;
  }

  public function __toString(): string {
    return $this->value->encode_as_php();
  }
}
