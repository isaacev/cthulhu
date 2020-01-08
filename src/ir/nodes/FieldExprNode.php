<?php

namespace Cthulhu\ir\nodes;

class FieldExprNode extends Node {
  public Name $name;
  public Expr $expr;

  public function __construct(Name $name, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->expr = $expr;
  }

  public function children(): array {
    return [
      $this->name,
      $this->expr,
    ];
  }
}
