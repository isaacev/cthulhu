<?php

namespace Cthulhu\IR;

class BinaryExpr extends Expr {
  public $type;
  public $operator;
  public $left;
  public $right;

  function __construct(Types\Type $type, string $operator, Expr $left, Expr $right) {
    $this->type     = $type;
    $this->operator = $operator;
    $this->left     = $left;
    $this->right    = $right;
  }

  public function return_type(): Types\Type {
    return $this->type;
  }
}
