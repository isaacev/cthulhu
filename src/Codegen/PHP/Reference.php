<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;
use Cthulhu\IR;

class Reference implements Buildable {
  public $symbol;
  public $segments;

  function __construct(IR\Symbol $symbol, array $segments) {
    $this->symbol = $symbol;
    $this->segments = $segments;
  }

  public function build(): Builder {
    return (new Builder)
      ->reference($this->segments);
  }
}
