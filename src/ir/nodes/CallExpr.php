<?php

namespace Cthulhu\ir\nodes;

class CallExpr extends Expr {
  public Expr $callee;
  public array $args;

  /**
   * @param Expr   $callee
   * @param Expr[] $args
   */
  public function __construct(Expr $callee, array $args) {
    parent::__construct();
    assert(($callee instanceof self) === false);
    assert(empty($args) === false);
    $this->callee = $callee;
    $this->args   = $args;
  }

  public function children(): array {
    return array_merge(
      [ $this->callee ],
      $this->args
    );
  }
}
