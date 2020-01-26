<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\Symbol;
use Cthulhu\loc\Spanlike;

class DecExpr extends Expr {
  public Symbol $name;
  public Type $note;

  public function __construct(Spanlike $spanlike, Symbol $name, Type $note) {
    parent::__construct($spanlike);
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
