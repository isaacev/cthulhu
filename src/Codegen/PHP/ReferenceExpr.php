<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class ReferenceExpr extends Expr {
  public $reference;

  function __construct(Reference $reference) {
    $this->reference = $reference;
  }

  public function build(): Builder {
    return (new Builder)
      ->backslash()
      ->then($this->reference);
  }
}
