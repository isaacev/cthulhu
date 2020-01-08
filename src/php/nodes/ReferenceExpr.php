<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ReferenceExpr extends Expr {
  public Reference $reference;
  public bool $is_quoted;

  public function __construct(Reference $reference, bool $is_quoted) {
    parent::__construct();
    $this->reference = $reference;
    $this->is_quoted = $is_quoted;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->maybe($this->is_quoted, (new Builder)
        ->single_quote())
      ->backslash()
      ->then($this->reference)
      ->maybe($this->is_quoted, (new Builder)
        ->single_quote());
  }
}
