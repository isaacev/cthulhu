<?php

namespace Cthulhu\IR;

class NumExpr extends Expr {
  public $type;
  public $value;

  function __construct(Types\Type $type, int $value) {
    $this->type  = $type;
    $this->value = $value;
  }

  function return_type(): Types\Type {
    return $this->type;
  }
}
