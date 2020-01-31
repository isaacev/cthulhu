<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Atomic;
use Cthulhu\val\FloatValue;

class FloatConstPattern extends ConstPattern {
  public FloatValue $value;

  public function __construct(FloatValue $value) {
    parent::__construct(Atomic::float());
    $this->value = $value;
  }

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }

  public function __toString(): string {
    return $this->value->encode_as_php();
  }
}
