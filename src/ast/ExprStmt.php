<?php

namespace Cthulhu\ast;

class ExprStmt extends Stmt {
  public Expr $expr;

  /**
   * @param Expr $expr
   * @param Attribute[] $attrs
   */
  function __construct(Expr $expr, array $attrs) {
    parent::__construct($expr->span, $attrs);
    $this->expr = $expr;
  }
}
