<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class IntLiteral extends Literal {
  public int $value;
  public string $raw;

  public function __construct(Span $span, int $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw   = $raw;
  }
}
