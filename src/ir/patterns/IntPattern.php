<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\val\IntegerValue;

class IntPattern extends Pattern {
  public IntegerValue $value;

  public function __construct(IntegerValue $value) {
    $this->value = $value;
  }

  public function __toString(): string {
    return $this->value->encode_as_php();
  }
}
