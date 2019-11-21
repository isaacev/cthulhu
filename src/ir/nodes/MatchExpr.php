<?php

namespace Cthulhu\ir\nodes;

class MatchExpr extends Expr {
  public $disc;
  public $arms;

  function __construct(MatchDiscriminant $disc, array $arms) {
    parent::__construct();
    $this->disc = $disc;
    $this->arms = $arms;
  }

  function children(): array {
    return array_merge(
      [ $this->disc ],
      $this->arms
    );
  }
}
