<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class VariableExpr extends Expr {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  public function build(): Builder {
    return (new Builder)
      ->variable($this->name);
  }

  public function jsonSerialize() {
    return [
      'type' => 'VarExpr',
      'name' => $this->name
    ];
  }
}
