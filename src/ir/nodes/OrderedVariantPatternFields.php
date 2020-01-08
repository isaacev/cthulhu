<?php

namespace Cthulhu\ir\nodes;

class OrderedVariantPatternFields extends VariantPatternFields {
  public array $order;

  /**
   * @param OrderedVariantPatternField[] $order
   */
  public function __construct(array $order) {
    parent::__construct();
    $this->order = $order;
  }

  public function children(): array {
    return $this->order;
  }

  public function __toString() {
    return '(' . implode(', ', $this->order) . ')';
  }
}
