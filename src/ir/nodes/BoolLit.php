<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Atomic;
use Cthulhu\val\BooleanValue;

class BoolLit extends Lit {
  public BooleanValue $bool_value;

  public function __construct(BooleanValue $value) {
    parent::__construct(Atomic::bool(), $value);
    $this->bool_value = $value;
  }
}
