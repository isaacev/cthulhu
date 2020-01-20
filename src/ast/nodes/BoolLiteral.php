<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\val\BooleanValue;

class BoolLiteral extends Literal {
  public BooleanValue $bool_value;

  public function __construct(BooleanValue $value) {
    parent::__construct($value);
    $this->bool_value = $value;
  }
}
