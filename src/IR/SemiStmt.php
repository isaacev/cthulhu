<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class SemiStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function jsonSerialize() {
    return [
      'type' => 'SemiStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
