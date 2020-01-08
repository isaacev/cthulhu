<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class EchoStmt extends Stmt {
  public Expr $expr;

  public function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  use traits\Unary;

  public function build(): Builder {
    return (new Builder)
      ->keyword('echo')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }
}
