<?php

namespace Cthulhu\ast\nodes;

class FieldAccessExpr extends Expr {
  public Expr $root;
  public LowerName $field;

  public function __construct(Expr $root, LowerName $field) {
    parent::__construct();
    $this->root  = $root;
    $this->field = $field;
  }

  public function children(): array {
    return [ $this->root, $this->field ];
  }
}
