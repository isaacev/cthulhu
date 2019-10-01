<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class EchoStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  use Traits\Unary;

  public function build(): Builder {
    return (new Builder)
      ->keyword('echo')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }
}
