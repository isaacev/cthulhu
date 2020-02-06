<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class Splat extends Expr {
  public Expr $expr;

  public function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  use traits\Unary;

  public function build(): Builder {
    return (new Builder)
      ->keyword('...')
      ->paren_left()
      ->then($this->expr)
      ->paren_right();
  }
}
