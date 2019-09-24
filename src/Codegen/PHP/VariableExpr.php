<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class VariableExpr extends Expr {
  public $variable;

  function __construct(Variable $variable) {
    $this->variable = $variable;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('VariableExpr', $table)) {
      $table['VariableExpr']($this);
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->variable);
  }
}
