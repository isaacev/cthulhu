<?php

namespace Cthulhu\IR;

class SemiStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }
}
