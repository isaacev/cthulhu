<?php

namespace Cthulhu\ir\nodes;

class MatchDiscriminant extends Node {
  public Expr $expr;

  public function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->expr ];
  }
}
