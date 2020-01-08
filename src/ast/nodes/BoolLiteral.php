<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;
use Cthulhu\val\BooleanValue;

class BoolLiteral extends Literal {
  public BooleanValue $value;

  public function __construct(Span $span, BooleanValue $value) {
    parent::__construct($span);
    $this->value = $value;
  }
}
