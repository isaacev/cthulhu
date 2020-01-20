<?php

namespace Cthulhu\ast\nodes;

class MatchExpr extends Expr {
  public Expr $discriminant;
  public array $arms;

  /**
   * @param Expr       $discriminant
   * @param MatchArm[] $arms
   */
  public function __construct(Expr $discriminant, array $arms) {
    parent::__construct();
    $this->discriminant = $discriminant;
    $this->arms         = $arms;
  }

  public function children(): array {
    return array_merge([ $this->discriminant ], $this->arms);
  }
}
