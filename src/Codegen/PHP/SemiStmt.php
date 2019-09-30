<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class SemiStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->expr)
      ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'SemiStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
