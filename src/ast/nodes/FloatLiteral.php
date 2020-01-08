<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;
use Cthulhu\val\FloatValue;

class FloatLiteral extends Literal {
  public FloatValue $value;

  public function __construct(Span $span, FloatValue $value) {
    parent::__construct($span);
    $this->value = $value;
  }
}
