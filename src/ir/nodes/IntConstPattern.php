<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Atomic;
use Cthulhu\val\IntegerValue;

class IntConstPattern extends ConstPattern {
  public IntegerValue $value;

  public function __construct(IntegerValue $value) {
    parent::__construct(Atomic::int());
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
