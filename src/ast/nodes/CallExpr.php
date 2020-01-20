<?php

namespace Cthulhu\ast\nodes;

class CallExpr extends Expr {
  public Expr $callee;
  public array $args;

  public function __construct(Expr $callee, array $args) {
    parent::__construct();
    $this->callee = $callee;
    $this->args   = $args;
  }

  public function children(): array {
    return array_merge([ $this->callee ], $this->args);
  }
}
