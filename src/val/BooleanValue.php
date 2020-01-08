<?php

namespace Cthulhu\val;

class BooleanValue extends Value {
  public string $raw;
  public bool $value;

  public function __construct(string $raw, bool $value) {
    $this->raw   = $raw;
    $this->value = $value;
  }

  public function encode_as_php(): string {
    return $this->value ? 'true' : 'false';
  }

  public static function from_scalar(bool $value): BooleanValue {
    return new BooleanValue($value ? 'true' : 'false', $value);
  }
}
