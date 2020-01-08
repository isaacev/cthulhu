<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\BooleanValue;

class BoolConstPattern extends ConstPattern {
  public BooleanValue $value;

  public function __construct(BooleanValue $value) {
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
