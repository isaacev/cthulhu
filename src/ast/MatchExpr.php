<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class MatchExpr extends Expr {
  public $disc;
  public $arms;

  function __construct(Source\Span $span, Expr $disc, array $arms) {
    parent::__construct($span);
    $this->disc = $disc;
    $this->arms = $arms;
  }
}
