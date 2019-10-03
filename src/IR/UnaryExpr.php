<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class UnaryExpr extends Expr {
  public $type;
  public $operator;
  public $operand;

  function __construct(Type $type, string $operator, Expr $operand) {
    $this->type = $type;
    $this->operator = $operator;
    $this->operand = $operand;
  }

  public function type(): Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'UnaryExpr'
    ];
  }
}
