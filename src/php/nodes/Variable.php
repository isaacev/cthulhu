<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Buildable;
use Cthulhu\php\Builder;

class Variable implements Buildable {
  public $value;

  function __construct(string $value) {
    $this->value = $value;
  }

  public function build(): Builder {
    return (new Builder)
      ->variable($this->value);
  }
}
