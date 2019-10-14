<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ReturnStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  use traits\Unary;

  public function build(): Builder {
    return (new Builder)
      ->keyword('return')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }
}
