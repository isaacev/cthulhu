<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UnaryExpr extends Expr {
  public string $operator;
  public Expr $operand;

  function __construct(Source\Span $span, string $operator, Expr $operand) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->operand = $operand;
  }
}
