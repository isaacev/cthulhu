<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class PipeExpr extends Expr {
  public Expr $left;
  public Expr $right;

  public function __construct(Span $span, Expr $left, Expr $right) {
    parent::__construct($span);
    $this->left  = $left;
    $this->right = $right;
  }
}
