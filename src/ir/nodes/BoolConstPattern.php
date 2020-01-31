<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Atomic;
use Cthulhu\val\BooleanValue;

class BoolConstPattern extends ConstPattern {
  public BooleanValue $value;

  public function __construct(BooleanValue $value) {
    parent::__construct(Atomic::bool());
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
