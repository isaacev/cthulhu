<?php

namespace Cthulhu\IR;

class UnaryExpr extends Expr {
  public $type;
  public $operator;
  public $operand;

  function __construct(Types\Type $type, string $operator, Expr $operand) {
    $this->type     = $type;
    $this->operator = $operator;
    $this->operand  = $operand;
  }

  function return_type(): Types\Type {
    return $this->type;
  }
}
