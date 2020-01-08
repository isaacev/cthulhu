<?php

namespace Cthulhu\ir\nodes;

class PipeExpr extends Expr {
  public Expr $left;
  public Expr $right;

  public function __construct(Expr $left, Expr $right) {
    parent::__construct();
    $this->left  = $left;
    $this->right = $right;
  }

  public function children(): array {
    return [ $this->left, $this->right ];
  }
}
