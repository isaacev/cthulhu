<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\Symbol;

class LetExpr extends Expr {
  public Symbol $name;
  public Expr $body;

  public function __construct(Symbol $name, Expr $body) {
    $this->name = $name;
    $this->body = $body;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->let()
      ->space()
      ->name($this->name)
      ->space()
      ->then($this->body)
      ->paren_right();
  }
}