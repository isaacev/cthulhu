<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\php\names;

class Reference extends Node {
  public $segments;
  public $symbol;

  function __construct(string $segments, names\Symbol $symbol) {
    parent::__construct();
    $this->segments = $segments;
    $this->symbol = $symbol;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->keyword($this->segments);
  }
}
