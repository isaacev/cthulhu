<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class OrderedVariantConstructorFields extends VariantConstructorFields {
  public array $order;

  /**
   * @param Span   $span
   * @param Expr[] $order
   */
  public function __construct(Span $span, array $order) {
    parent::__construct($span);
    $this->order = $order;
  }
}
