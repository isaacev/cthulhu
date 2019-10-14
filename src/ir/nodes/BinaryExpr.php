<?php

namespace Cthulhu\ir\nodes;

class BinaryExpr extends Expr {
  public $op;
  public $left;
  public $right;

  function __construct(string $op, Expr $left, Expr $right) {
    parent::__construct();
    $this->op    = $op;
    $this->left  = $left;
    $this->right = $right;
  }

  function children(): array {
    return [
      $this->left,
      $this->right,
    ];
  }
}
