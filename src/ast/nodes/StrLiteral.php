<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\val\StringValue;

class StrLiteral extends Literal {
  public StringValue $str_value;

  public function __construct(StringValue $value) {
    parent::__construct($value);
    $this->str_value = $value;
  }
}
