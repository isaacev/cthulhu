<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class VariableExpr extends Expr {
  public $variable;

  function __construct(Variable $variable) {
    $this->variable = $variable;
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->variable);
  }
}
