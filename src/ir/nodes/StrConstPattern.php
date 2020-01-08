<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\StringValue;

class StrConstPattern extends ConstPattern {
  public StringValue $value;

  public function __construct(StringValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return $this->value->encode_as_php();
  }
}
