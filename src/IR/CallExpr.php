<?php

namespace Cthulhu\IR;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Expr $callee, array $args) {
    $this->callee = $callee;
    $this->args   = $args;
  }

  public function return_type(): Types\Type {
    return $this->callee->return_type()->output;
  }
}
