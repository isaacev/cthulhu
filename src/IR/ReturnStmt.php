<?php

namespace Cthulhu\IR;

class ReturnStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }
}
