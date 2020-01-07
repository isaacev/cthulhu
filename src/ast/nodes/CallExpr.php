<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class CallExpr extends Expr {
  public Expr $callee;
  public array $args;

  public function __construct(Span $span, Expr $callee, array $args) {
    parent::__construct($span);
    $this->callee = $callee;
    $this->args   = $args;
  }
}
