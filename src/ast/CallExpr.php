<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class CallExpr extends Expr {
  public Expr $callee;
  public array $args;

  function __construct(Source\Span $span, Expr $callee, array $args) {
    parent::__construct($span);
    $this->callee = $callee;
    $this->args   = $args;
  }
}
