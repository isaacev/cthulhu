<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Atomic;
use Cthulhu\val\StringValue;

class StrConstPattern extends ConstPattern {
  public StringValue $value;

  public function __construct(StringValue $value) {
    parent::__construct(Atomic::str());
    $this->value = $value;
  }

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }

  public function __toString(): string {
    return $this->value->encode_as_php();
  }
}
