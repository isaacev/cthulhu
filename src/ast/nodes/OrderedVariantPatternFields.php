<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class OrderedVariantPatternFields extends VariantPatternFields {
  public array $order;

  /**
   * @param Span      $span
   * @param Pattern[] $order
   */
  public function __construct(Span $span, array $order) {
    parent::__construct($span);
    $this->order = $order;
  }
}
