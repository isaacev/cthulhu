<?php

namespace Cthulhu\ir\nodes;

/**
 * @property Expr   $callee
 * @property Expr[] $args
 */
class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Expr $callee, array $args) {
    parent::__construct();
    $this->callee = $callee;
    $this->args   = $args;
  }

  function children(): array {
    return array_merge([ $this->callee ], $this->args);
  }
}
