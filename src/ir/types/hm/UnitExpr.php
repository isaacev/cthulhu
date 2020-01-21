<?php

namespace Cthulhu\ir\types\hm;

class UnitExpr extends Expr {
  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('unit')
      ->paren_right();
  }
}
