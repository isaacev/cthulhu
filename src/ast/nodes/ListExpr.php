<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class ListExpr extends Expr {
  public array $elements;

  /**
   * @param Span   $span
   * @param Expr[] $elements
   */
  public function __construct(Span $span, array $elements) {
    parent::__construct($span);
    $this->elements = $elements;
  }
}
