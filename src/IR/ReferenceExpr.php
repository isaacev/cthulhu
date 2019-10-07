<?php

namespace Cthulhu\IR;

class ReferenceExpr extends Expr {
  public $type;
  public $symbol;

  function __construct(Types\Type $type, Symbol $symbol) {
    $this->type   = $type;
    $this->symbol = $symbol;
  }

  function return_type(): Types\Type {
    return $this->type;
  }
}
