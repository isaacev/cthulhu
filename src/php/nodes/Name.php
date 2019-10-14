<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Buildable;
use Cthulhu\php\Builder;

class Name implements Buildable {
  public $value;

  function __construct(string $value) {
    $this->value = $value;
  }

  public function build(): Builder {
    return (new Builder)
      ->identifier($this->value);
  }
}
