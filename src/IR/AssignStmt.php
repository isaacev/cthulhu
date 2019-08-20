<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class AssignStmt extends Stmt {
  public $name;
  public $symbol;
  public $expr;

  function __construct(string $name, Symbol $symbol, Expr $expr) {
    $this->name = $name;
    $this->symbol = $symbol;
    $this->expr = $expr;
  }

  public function type(): Types\Type {
    return new Types\VoidType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'AssignStmt',
      'name' => $this->name,
      'symbol' => $this->symbol->jsonSerialize(),
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
