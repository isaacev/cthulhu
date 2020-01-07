<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class UnaryExpr extends Expr {
  public string $operator;
  public Expr $operand;

  public function __construct(Span $span, string $operator, Expr $operand) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->operand  = $operand;
  }
}
