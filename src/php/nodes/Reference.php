<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Buildable;
use Cthulhu\php\Builder;

class Reference implements Buildable {
  public $segments;

  function __construct(array $segments) {
    $this->segments = $segments;
  }

  public function build(): Builder {
    return (new Builder)
      ->reference($this->segments);
  }
}
