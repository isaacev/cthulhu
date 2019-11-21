<?php

namespace Cthulhu\ir\nodes;

class MatchDiscriminant extends Node {
  public $expr;

  function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  function children(): array {
    return [ $this->expr ];
  }
}
