<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR;

class FunctionScope extends Scope {
  public $parent;
  public $free_variables;

  function __construct(Scope $parent) {
    $this->parent = $parent;
    $this->params = [];
    $this->free_variables = [];
  }

  public function has_variable(IR\Symbol $symbol): bool {
    return parent::has_symbol_in_table($symbol);
  }

  public function register_variable(IR\IdentifierNode $ident): Variable {
    parent::add_symbol_to_table($ident->symbol, $ident->name);
    return new Variable($ident->name);
  }

  public function get_variable(IR\Symbol $symbol): Variable {
    if ($this->has_variable($symbol)) {
      return new Variable(parent::get_name_from_table($symbol));
    }

    if ($this->parent instanceof FunctionScope) {
      return $this->free_variables[] = $this->parent->get_variable($symbol);
    } else {
      throw new \Exception('cannot use lexical scope from top-level function');
    }
  }

  public function set_params(array $params): void {
    foreach ($params as $param) {
      $this->register_variable($param->identifier);
      $this->params[] = $this->get_variable($param->identifier->symbol);
    }
  }

  public function get_params(): array {
    return $this->params;
  }

  public function get_free_variables(): array {
    return $this->free_variables;
  }
}
