<?php

namespace Cthulhu\ast\nodes;

class UnaryExpr extends Expr {
  public OperatorRef $operator;
  public Expr $right;

  public function __construct(OperatorRef $operator, Expr $right) {
    parent::__construct();
    $this->operator = $operator;
    $this->right    = $right;
  }

  public function children(): array {
    return [ $this->operator, $this->right ];
  }
}
