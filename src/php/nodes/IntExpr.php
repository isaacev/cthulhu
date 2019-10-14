<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class IntExpr extends Expr {
  public $value;

  function __construct(int $value) {
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->int_literal($this->value);
  }
}
