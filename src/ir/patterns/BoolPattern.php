<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\val\BooleanValue;

class BoolPattern extends Pattern {
  public BooleanValue $value;

  function __construct(BooleanValue $value) {
    $this->value = $value;
  }

  function __toString(): string {
    return $this->value->encode_as_php();
  }
}
