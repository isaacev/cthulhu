<?php

namespace Cthulhu\IR;

class StrExpr extends Expr {
  public $type;
  public $value;

  function __construct(Types\Type $type, string $value) {
    $this->type  = $type;
    $this->value = $value;
  }

  function return_type(): Types\Type {
    return $this->type;
  }
}
