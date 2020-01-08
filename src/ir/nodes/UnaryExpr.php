<?php

namespace Cthulhu\ir\nodes;

class UnaryExpr extends Expr {
  public string $op;
  public Expr $right;

  public function __construct(string $op, Expr $right) {
    parent::__construct();
    $this->op    = $op;
    $this->right = $right;
  }

  public function children(): array {
    return [ $this->right ];
  }
}
