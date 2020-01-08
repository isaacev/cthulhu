<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\IntegerValue;

class IntConstPattern extends ConstPattern {
  public IntegerValue $value;

  public function __construct(IntegerValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return $this->value->encode_as_php();
  }
}
