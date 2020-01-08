<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\FloatValue;

class FloatLiteral extends Literal {
  public FloatValue $value;

  public function __construct(FloatValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }
}
