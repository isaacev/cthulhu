<?php

namespace Cthulhu\ir\nodes;

class Handler extends Node {
  public Expr $expr;

  public function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $children): Handler {
    return new Handler(...$children);
  }

  public function build(): Builder {
    return $this->expr->build();
  }
}
