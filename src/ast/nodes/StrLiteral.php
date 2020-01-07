<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class StrLiteral extends Literal {
  public string $value;
  public string $raw;

  public function __construct(Span $span, string $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw   = $raw;
  }
}
