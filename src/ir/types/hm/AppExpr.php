<?php

namespace Cthulhu\ir\types\hm;

class AppExpr extends Expr {
  public Expr $func;
  public Expr $arg;

  public function __construct(Expr $func, Expr $arg) {
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
