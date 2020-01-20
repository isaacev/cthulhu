<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\val\IntegerValue;

class IntLiteral extends Literal {
  public IntegerValue $int_value;

  public function __construct(IntegerValue $value) {
    parent::__construct($value);
    $this->int_value = $value;
  }
}
