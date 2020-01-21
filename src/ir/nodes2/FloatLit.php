<?php

namespace Cthulhu\ir\nodes2;

use Cthulhu\ir\types\hm;
use Cthulhu\val\FloatValue;

class FloatLit extends Lit {
  public FloatValue $float_value;

  public function __construct(FloatValue $value) {
    parent::__construct(new hm\Nullary('Float'), $value);
    $this->float_value = $value;
  }
}
