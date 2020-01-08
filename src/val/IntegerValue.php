<?php

namespace Cthulhu\val;

class IntegerValue extends Value {
  public string $raw;
  public int $value;

  public function __construct(string $raw, int $value) {
    $this->raw   = $raw;
    $this->value = $value;
  }

  public function encode_as_php(): string {
    return strval($this->value);
  }

  public static function from_scalar(int $value): IntegerValue {
    return new IntegerValue("$value", $value);
  }
}
