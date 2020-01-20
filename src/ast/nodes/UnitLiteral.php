<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\val\UnitValue;

class UnitLiteral extends Literal {
  public function __construct() {
    parent::__construct(new UnitValue());
  }
}
