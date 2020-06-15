<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ConstantExpr extends Expr {
  public string $name;

  public function __construct(string $name) {
    parent::__construct();
    $this->name = $name;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->keyword($this->name);
  }
}
