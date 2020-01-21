<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\Symbol;

class DecExpr extends Expr {
  public Symbol $name;
  public Type $note;

  public function __construct(Symbol $name, Type $note) {
    $this->name = $name;
    $this->note = $note;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->let()
      ->space()
      ->name($this->name)
      ->space()
      ->type($this->note)
      ->paren_right();
  }
}
