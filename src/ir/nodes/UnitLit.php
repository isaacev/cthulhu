<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\hm\Unit;
use Cthulhu\val\UnitValue;

class UnitLit extends Lit {
  public function __construct() {
    parent::__construct(new Unit(), new UnitValue());
  }
}
