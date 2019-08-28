<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;

class Builtin implements Buildable {
  public $builder;

  function __construct(Builder $builder) {
    $this->builder = $builder;
  }

  public function build(): Builder {
    return $this->builder;
  }
}
