<?php

namespace Cthulhu\IR;

class BoolExpr extends Expr {
  public $type;
  public $value;

  function __construct(Types\Type $type, bool $value) {
    $this->type  = $type;
    $this->value = $value;
  }

  function return_type(): Types\Type {
    return $this->type;
  }
}
