<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\val\IntegerValue;

class IntLiteral extends Literal {
  public IntegerValue $value;

  public function __construct(IntegerValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }
}
