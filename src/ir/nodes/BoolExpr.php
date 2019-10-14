<?php

namespace Cthulhu\ir\nodes;

class BoolExpr extends Expr {
  public $value;

  function __construct(bool $value) {
    parent::__construct();
    $this->value = $value;
  }

  function children(): array {
    return [];
  }
}
