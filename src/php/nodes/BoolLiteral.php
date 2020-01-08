<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\val\BooleanValue;

class BoolLiteral extends Literal {
  public BooleanValue $value;

  function __construct(BooleanValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }
}
