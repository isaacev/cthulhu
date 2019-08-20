<?php

namespace Cthulhu\IR;

class ReturnStmt extends Stmt {
  public $expr;

  function __construct(IR\Expr $expr) {
    $this->expr = $expr;
  }

  public function jsonSerialize() {
    return [
      'type' => 'ReturnStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
