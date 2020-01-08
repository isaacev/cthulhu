<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\FloatValue;

class FloatConstPattern extends ConstPattern {
  public FloatValue $value;

  function __construct(FloatValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  function __toString(): string {
    return $this->value->encode_as_php();
  }
}
