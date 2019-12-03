<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class BinaryExpr extends Expr {
  public string $operator;
  public Expr $left;
  public Expr $right;

  function __construct(Source\Span $span, string $operator, Expr $left, Expr $right) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->left     = $left;
    $this->right    = $right;
  }
}
