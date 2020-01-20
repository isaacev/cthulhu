<?php

namespace Cthulhu\ast\nodes;

class SemiStmt extends Stmt {
  public Expr $expr;

  public function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->expr ];
  }
}
