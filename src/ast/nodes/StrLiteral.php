<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;
use Cthulhu\val\StringValue;

class StrLiteral extends Literal {
  public StringValue $value;

  public function __construct(Span $span, StringValue $value) {
    parent::__construct($span);
    $this->value = $value;
  }
}
