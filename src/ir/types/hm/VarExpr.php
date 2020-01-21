<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\Symbol;

class VarExpr extends Expr {
  public Symbol $name;

  public function __construct(Symbol $name) {
    $this->name = $name;
  }

  public function build(): Builder {
    return (new Builder)
      ->name($this->name);
  }
}
