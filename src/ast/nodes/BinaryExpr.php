<?php

namespace Cthulhu\ast\nodes;

class BinaryExpr extends Expr {
  public OperatorRef $operator;
  public Expr $left;
  public Expr $right;

  public function __construct(OperatorRef $operator, Expr $left, Expr $right) {
    parent::__construct();
    $this->operator = $operator;
    $this->left     = $left;
    $this->right    = $right;
  }

  public function children(): array {
    return [ $this->operator, $this->left, $this->right ];
  }
}
