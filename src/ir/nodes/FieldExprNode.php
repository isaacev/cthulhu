<?php

namespace Cthulhu\ir\nodes;

class FieldExprNode extends Node {
  public Name $name;
  public Expr $expr;

  function __construct(Name $name, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->expr = $expr;
  }

  function children(): array {
    return [
      $this->name,
      $this->expr,
    ];
  }
}
