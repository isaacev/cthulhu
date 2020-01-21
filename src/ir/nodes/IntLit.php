<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\hm;
use Cthulhu\val\IntegerValue;

class IntLit extends Lit {
  public IntegerValue $int_value;

  public function __construct(IntegerValue $value) {
    parent::__construct(new hm\Nullary('Int'), $value);
    $this->int_value = $value;
  }
}
