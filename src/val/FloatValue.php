<?php

namespace Cthulhu\val;

use function number_format;

class FloatValue extends Value {
  public string $raw;
  public float $value;
  public int $precision;

  public function __construct(string $raw, float $value, int $precision) {
    $this->raw       = $raw;
    $this->value     = $value;
    $this->precision = $precision;
  }

  public function encode_as_php(): string {
    return number_format($this->value, $this->precision, ".", "");
  }
}
