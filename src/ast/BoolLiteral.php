<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class BoolLiteral extends Literal {
  public $value;
  public $raw;

  function __construct(Source\Span $span, bool $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }
}
