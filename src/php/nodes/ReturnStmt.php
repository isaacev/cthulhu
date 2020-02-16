<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ReturnStmt extends Stmt {
  public ?Expr $expr;

  public function __construct(Expr $expr, ?Stmt $next) {
    parent::__construct($next);
    $this->expr = $expr;
  }

  use traits\Unary;

  public function build(): Builder {
    $expr = (new Builder);
    if ($this->expr) {
      $expr
        ->space()
        ->then($this->expr);
    }

    return (new Builder)
      ->newline_then_indent()
      ->keyword('return')
      ->then($expr)
      ->semicolon()
      ->then($this->next ?? (new Builder));
  }
}
