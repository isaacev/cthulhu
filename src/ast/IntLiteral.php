<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class IntLiteral extends Literal {
  public int $value;
  public string $raw;

  function __construct(Source\Span $span, int $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw   = $raw;
  }
}
