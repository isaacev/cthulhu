<?php

namespace Cthulhu\ast\nodes;

class CallExpr extends Expr {
  public Expr $callee;
  public Exprs $args;

  public function __construct(Expr $callee, Exprs $args) {
    parent::__construct();
    $this->callee = $callee;
    $this->args   = $args;
  }

  public function children(): array {
    return [ $this->callee, $this->args ];
  }
}
