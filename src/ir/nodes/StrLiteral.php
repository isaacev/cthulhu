<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\val\StringValue;

class StrLiteral extends Literal {
  public StringValue $value;

  public function __construct(StringValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }
}
