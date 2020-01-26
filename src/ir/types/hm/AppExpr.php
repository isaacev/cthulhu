<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Spanlike;

class AppExpr extends Expr {
  public Expr $func;
  public Expr $arg;

  public function __construct(Spanlike $spanlike, Expr $func, Expr $arg) {
    parent::__construct($spanlike);
    $this->func = $func;
    $this->arg  = $arg;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->then($this->func)
      ->space()
      ->then($this->arg)
      ->paren_right();
  }
}
