<?php

namespace Cthulhu\ir\nodes;

class OrderedVariantPatternFields extends VariantPatternFields {
  public array $order;

  /**
   * @param OrderedVariantPatternField[] $order
   */
  function __construct(array $order) {
    parent::__construct();
    $this->order = $order;
  }

  function children(): array {
    return $this->order;
  }

  function __toString() {
    return '(' . implode(', ', $this->order) . ')';
  }
}
