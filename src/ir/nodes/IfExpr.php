<?php

namespace Cthulhu\ir\nodes;

class IfExpr extends Expr {
  public $cond;
  public $if_true;
  public $if_false;

  function __construct(Expr $cond, Block $if_true, ?Block $if_false) {
    parent::__construct();
    $this->cond     = $cond;
    $this->if_true  = $if_true;
    $this->if_false = $if_false;
  }

  function children(): array {
    return [
      $this->cond,
      $this->if_true,
      $this->if_false,
    ];
  }
}
