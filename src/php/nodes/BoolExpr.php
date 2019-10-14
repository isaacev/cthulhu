<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class BoolExpr extends Expr {
  public $value;

  function __construct(bool $value) {
    parent::__construct();
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->bool_literal($this->value);
  }
}
