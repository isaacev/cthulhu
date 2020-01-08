<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\val\StringValue;

class StrPattern extends Pattern {
  public StringValue $value;

  public function __construct(StringValue $value) {
    $this->value = $value;
  }

  public function __toString(): string {
    return $this->value->encode_as_php();
  }
}
