<?php

namespace Cthulhu\ir\nodes2;

use Cthulhu\ir\types\hm;
use Cthulhu\val\StringValue;

class StrLit extends Lit {
  public StringValue $str_value;

  public function __construct(StringValue $value) {
    parent::__construct(new hm\Nullary('Str'), $value);
    $this->str_value = $value;
  }
}
