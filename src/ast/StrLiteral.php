<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class StrLiteral extends Literal {
  public string $value;
  public string $raw;

  function __construct(Source\Span $span, string $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw   = $raw;
  }
}
