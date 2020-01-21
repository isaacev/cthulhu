<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\val\Value;

class LitExpr extends Expr {
  public Value $value;

  public function __construct(Value $value) {
    $this->value = $value;
  }

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }
}
