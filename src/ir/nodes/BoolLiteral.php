<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\BooleanValue;

class BoolLiteral extends Literal {
  public BooleanValue $value;

  public function __construct(BooleanValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }
}
