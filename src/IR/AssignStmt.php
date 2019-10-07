<?php

namespace Cthulhu\IR;

class AssignStmt extends Stmt {
  public $symbol;
  public $expr;

  function __construct(Symbol $symbol, Expr $expr) {
    $this->symbol = $symbol;
    $this->expr   = $expr;
  }
}
