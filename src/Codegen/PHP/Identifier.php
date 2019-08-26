<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;

class Identifier implements Buildable {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  public function build(): Builder {
    return (new Builder)
      ->identifier($this->name);
  }
}
