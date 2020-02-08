<?php

namespace Cthulhu\val;

class IntegerValue extends Value {
  public string $raw;
  public int $value;

  public function __construct(string $raw, int $value) {
    $this->raw   = $raw;
    $this->value = $value;
  }

  public function add(IntegerValue $other): IntegerValue {
    $value = $this->value + $other->value;
    $raw   = "$value";
    return new IntegerValue($raw, $value);
  }

  public function subtract(IntegerValue $other): IntegerValue {
    $value = $this->value - $other->value;
    $raw   = "$value";
    return new IntegerValue($raw, $value);
  }

  public function multiply(IntegerValue $other): IntegerValue {
    $value = $this->value * $other->value;
    $raw   = "$value";
    return new IntegerValue($raw, $value);
  }

  public function encode_as_php(): string {
    return strval($this->value);
  }

  public static function from_scalar(int $value): IntegerValue {
    return new IntegerValue("$value", $value);
  }
}
