<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class OrderedVariantPatternFields extends VariantPatternFields {
  public $order;

  /**
   * @param Source\Span $span
   * @param Pattern[] $order
   */
  function __construct(Source\Span $span, array $order) {
    parent::__construct($span);
    $this->order = $order;
  }
}
