<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\FloatValue;

class FloatLiteral extends Literal {
  public FloatValue $value;

  function __construct(FloatValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  function children(): array {
    return [];
  }
}
