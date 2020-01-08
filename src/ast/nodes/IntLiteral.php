<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;
use Cthulhu\val\IntegerValue;

class IntLiteral extends Literal {
  public IntegerValue $value;

  public function __construct(Span $span, IntegerValue $value) {
    parent::__construct($span);
    $this->value = $value;
  }
}
