<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class BoolLiteral extends Literal {
  public bool $value;
  public string $raw;

  public function __construct(Span $span, bool $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw   = $raw;
  }
}
