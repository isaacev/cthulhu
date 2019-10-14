<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ReferenceExpr extends Expr {
  public $reference;

  function __construct(Reference $reference) {
    $this->reference = $reference;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->backslash()
      ->then($this->reference);
  }
}
