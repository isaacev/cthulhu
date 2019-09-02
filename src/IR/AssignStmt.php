<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class AssignStmt extends Stmt {
  public $symbol;
  public $expr;

  function __construct(Symbol $symbol, Expr $expr) {
    $this->symbol = $symbol;
    $this->expr = $expr;
  }

  public function type(): Types\Type {
    return new Types\VoidType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'AssignStmt',
      'symbol' => $this->symbol,
      'expr' => $this->expr
    ];
  }
}
