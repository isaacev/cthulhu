<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\val\StringValue;

class StrLiteral extends Literal {
  public StringValue $value;

  public function __construct(StringValue $value) {
    parent::__construct();
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }
}
