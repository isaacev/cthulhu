<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class MatchExpr extends Expr {
  public Expr $disc;
  public array $arms;

  /**
   * @param Span       $span
   * @param Expr       $disc
   * @param MatchArm[] $arms
   */
  public function __construct(Span $span, Expr $disc, array $arms) {
    parent::__construct($span);
    $this->disc = $disc;
    $this->arms = $arms;
  }
}
