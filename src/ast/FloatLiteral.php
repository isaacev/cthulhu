<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FloatLiteral extends Literal {
  public $value;
  public $precision;
  public $raw;

  function __construct(Source\Span $span, float $value, int $precision, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->precision = $precision;
    $this->raw = $raw;
  }
}
