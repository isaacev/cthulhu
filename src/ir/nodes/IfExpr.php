<?php

namespace Cthulhu\ir\nodes;

class IfExpr extends Expr {
  public Expr $cond;
  public Block $if_true;
  public ?Block $if_false;

  public function __construct(Expr $cond, Block $if_true, ?Block $if_false) {
    parent::__construct();
    $this->cond     = $cond;
    $this->if_true  = $if_true;
    $this->if_false = $if_false;
  }

  public function children(): array {
    return [
      $this->cond,
      $this->if_true,
      $this->if_false,
    ];
  }
}
