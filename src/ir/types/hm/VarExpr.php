<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\Symbol;
use Cthulhu\loc\Spanlike;

class VarExpr extends Expr {
  public Symbol $name;

  public function __construct(Spanlike $spanlike, Symbol $name) {
    parent::__construct($spanlike);
    $this->name = $name;
  }

  public function build(): Builder {
    return (new Builder)
      ->name($this->name);
  }
}
