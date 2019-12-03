<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class MatchExpr extends Expr {
  public Expr $disc;
  public array $arms;

  /**
   * @param Source\Span $span
   * @param Expr        $disc
   * @param MatchArm[]  $arms
   */
  function __construct(Source\Span $span, Expr $disc, array $arms) {
    parent::__construct($span);
    $this->disc = $disc;
    $this->arms = $arms;
  }
}
