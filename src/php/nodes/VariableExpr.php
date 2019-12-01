<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class VariableExpr extends Expr {
  public Variable $variable;

  function __construct(Variable $variable) {
    parent::__construct();
    $this->variable = $variable;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->then($this->variable);
  }
}
