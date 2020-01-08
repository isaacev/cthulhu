<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\IntegerValue;

class IntLiteral extends Literal {
  public IntegerValue $value;

  public function __construct(IntegerValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }
}
