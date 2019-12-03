<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class OrderedVariantConstructorFields extends VariantConstructorFields {
  public array $order;

  /**
   * @param Source\Span $span
   * @param Expr[]      $order
   */
  function __construct(Source\Span $span, array $order) {
    parent::__construct($span);
    $this->order = $order;
  }
}
