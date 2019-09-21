<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;
use Cthulhu\IR;

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
