<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class PipeExpr extends Expr {
  public Expr $left;
  public Expr $right;

  function __construct(Source\Span $span, Expr $left, Expr $right) {
    parent::__construct($span);
    $this->left  = $left;
    $this->right = $right;
  }
}
