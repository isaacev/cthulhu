<?php

namespace Cthulhu\ir\nodes2;

use Cthulhu\ir\types\hm\Nullary;
use Cthulhu\val\UnitValue;

class UnitLit extends Lit {
  public function __construct() {
    parent::__construct(new Nullary('Unit'), new UnitValue());
  }
}
