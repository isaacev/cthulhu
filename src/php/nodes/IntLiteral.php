<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class IntLiteral extends Literal {
  public int $value;

  function __construct(int $value) {
    parent::__construct();
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->int_literal($this->value);
  }
}
