<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class ReferenceExpr extends Expr {
  public $segments;

  function __construct(array $segments) {
    $this->segments = $segments;
  }

  public function length(): int {
    return count($this->segments);
  }

  public function build(): Builder {
    return (new Builder)
      ->reference($this->segments);
  }
}
