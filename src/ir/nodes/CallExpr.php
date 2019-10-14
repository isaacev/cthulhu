<?php

namespace Cthulhu\ir\nodes;

/**
 * @property Expr   $callee
 * @property Expr[] $args
 */
class CallExpr extends Expr {
  public $callee;
  public $concretes;
  public $args;

  function __construct(Expr $callee, array $concretes, array $args) {
    parent::__construct();
    $this->callee = $callee;
    $this->concretes  = $concretes;
    $this->args   = $args;
  }

  function children(): array {
    return array_merge([ $this->callee ], $this->concretes, $this->args);
  }
}
