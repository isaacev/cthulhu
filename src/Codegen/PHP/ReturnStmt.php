<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class ReturnStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  use Traits\Unary;

  public function build(): Builder {
    return (new Builder)
      ->keyword('return')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ReturnStmt'
    ];
  }
}
