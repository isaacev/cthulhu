<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class SemiStmt extends Stmt {
  public Expr $expr;

  public function __construct(Expr $expr, ?Stmt $next) {
    parent::__construct($next);
    $this->expr = $expr;
  }

  use traits\Unary;

  public function build(): Builder {
    return (new Builder)
      ->newline_then_indent()
      ->expr($this->expr)
      ->semicolon()
      ->then($this->next ?? (new Builder));
  }
}
