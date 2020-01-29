<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Atomic;
use Cthulhu\val\UnitValue;

class UnitLit extends Lit {
  public function __construct() {
    parent::__construct(Atomic::unit(), new UnitValue());
  }
}
