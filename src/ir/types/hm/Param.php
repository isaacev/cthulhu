<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\Symbol;
use Cthulhu\lib\fmt\Buildable;

class Param implements Buildable {
  public Symbol $name;
  public Type $note;

  public function __construct(Symbol $name, Type $note = null) {
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
