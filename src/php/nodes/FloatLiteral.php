<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\val\FloatValue;

class FloatLiteral extends Literal {
  public FloatValue $value;

  public function __construct(FloatValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }
}
