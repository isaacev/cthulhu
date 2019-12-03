<?php

namespace Cthulhu\ir\nodes;

class CallExpr extends Expr {
  public Expr $callee;
  public array $args;

  /**
   * @param Expr   $callee
   * @param Expr[] $args
   */
  function __construct(Expr $callee, array $args) {
    parent::__construct();
    $this->callee = $callee;
    $this->args   = $args;
  }

  function children(): array {
    return array_merge(
      [ $this->callee ],
      $this->args
    );
  }
}
