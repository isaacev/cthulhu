<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Spanlike;
use Cthulhu\val\Value;

class LitExpr extends Expr {
  public Value $value;

  public function __construct(Spanlike $spanlike, Value $value) {
    parent::__construct($spanlike);
    $this->value = $value;
  }

  public function build(): Builder {
    return (new Builder)
      ->value($this->value);
  }
}
