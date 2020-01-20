<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\val\FloatValue;

class FloatLiteral extends Literal {
  public FloatValue $float_value;

  public function __construct(FloatValue $value) {
    parent::__construct($value);
    $this->float_value = $value;
  }
}
