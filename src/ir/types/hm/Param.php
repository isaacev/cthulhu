<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\Symbol;
use Cthulhu\lib\fmt\Buildable;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

class Param implements Buildable {
  public Span $span;
  public Symbol $name;
  public Type $note;

  public function __construct(Spanlike $spanlike, Symbol $name, Type $note = null) {
    $this->span = $spanlike->span();
    $this->name = $name;
    $this->note = $note;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->name($this->name)
      ->space()
      ->type($this->note)
      ->paren_right();
  }
}
