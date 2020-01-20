<?php

namespace Cthulhu\ast\nodes;

class FieldExprNode extends Node {
  public LowerName $name;
  public Expr $expr;

  public function __construct(LowerName $name, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->name, $this->expr ];
  }
}
