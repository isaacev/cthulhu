<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ThisExpr extends Expr {
  use traits\Atomic;

  function build(): Builder {
    return (new Builder)
      ->variable('this');
  }
}
