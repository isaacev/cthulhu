<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\php\names;

class Variable extends Node {
  public $value;
  public $symbol;

  function __construct(string $value, names\Symbol $symbol) {
    parent::__construct();
    $this->value = $value;
    $this->symbol = $symbol;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->variable($this->value);
  }
}
