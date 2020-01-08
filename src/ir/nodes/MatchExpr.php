<?php

namespace Cthulhu\ir\nodes;

class MatchExpr extends Expr {
  public MatchDiscriminant $disc;
  public array $arms;

  /**
   * @param MatchDiscriminant $disc
   * @param MatchArm[]        $arms
   */
  public function __construct(MatchDiscriminant $disc, array $arms) {
    parent::__construct();
    $this->disc = $disc;
    $this->arms = $arms;
  }

  public function children(): array {
    return array_merge(
      [ $this->disc ],
      $this->arms
    );
  }
}
