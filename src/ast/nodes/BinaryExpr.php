<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class BinaryExpr extends Expr {
  public string $operator;
  public Expr $left;
  public Expr $right;

  public function __construct(Span $span, string $operator, Expr $left, Expr $right) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->left     = $left;
    $this->right    = $right;
  }
}
