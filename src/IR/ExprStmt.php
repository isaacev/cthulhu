<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class ExprStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function type(): Type {
    return $this->expr->type();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ExprStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
