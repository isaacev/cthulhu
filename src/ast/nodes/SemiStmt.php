<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\ast\PunctToken;

class SemiStmt extends Stmt {
  public Expr $expr;
  public PunctToken $semi;

  /**
   * @param Expr        $expr
   * @param PunctToken  $semi
   * @param Attribute[] $attrs
   */
  public function __construct(Expr $expr, PunctToken $semi, array $attrs) {
    parent::__construct($expr->span->join($semi->span), $attrs);
    $this->expr = $expr;
    $this->semi = $semi;
  }
}
