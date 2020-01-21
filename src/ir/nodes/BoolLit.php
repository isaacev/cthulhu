<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\hm;
use Cthulhu\val\BooleanValue;

class BoolLit extends Lit {
  public BooleanValue $bool_value;

  public function __construct(BooleanValue $value) {
    parent::__construct(new hm\Nullary('Bool'), $value);
    $this->bool_value = $value;
  }
}
