<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR;

class FuncScope extends Scope {
  public $parent_scope;
  public $free_variables = [];

  function __construct(Scope $parent_scope) {
    $this->parent_scope = $parent_scope;
  }

  public function get_variable(IR\Symbol $symbol): string {
    if ($this->has_local_variable($symbol)) {
      return parent::get_variable($symbol);
    }

    $free_variable = $this->parent_scope->get_variable($symbol);
    $this->free_variables[] = $free_variable;
    return $free_variable;
  }
}
