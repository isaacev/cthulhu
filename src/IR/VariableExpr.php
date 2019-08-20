<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class VariableExpr extends Expr {
  public $name;
  public $symbol;

  function __construct(string $name, Symbol $symbol) {
    $this->name = $name;
    $this->symbol = $symbol;
  }

  public function type(): Type {
    return $this->symbol->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'VariableExpr',
      'name' => $this->name,
      'symbol' => $this->symbol->jsonSerialize()
    ];
  }
}
