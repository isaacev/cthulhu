<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Atomic;
use Cthulhu\val\StringValue;

class StrLit extends Lit {
  public StringValue $str_value;

  public function __construct(StringValue $value) {
    parent::__construct(Atomic::str(), $value);
    $this->str_value = $value;
  }
}
