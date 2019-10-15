<?php

namespace Cthulhu\ast;

class ExprStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr, array $attrs) {
    parent::__construct($expr->span, $attrs);
    $this->expr = $expr;
  }
}
