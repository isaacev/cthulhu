<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;
use Cthulhu\IR;

class Variable implements Buildable {
  public $symbol;
  public $name;

  function __construct(IR\Symbol $symbol, string $name) {
    $this->symbol = $symbol;
    $this->name = $name;
  }

  public function build(): Builder {
    return (new Builder)
      ->variable($this->name);
  }
}
