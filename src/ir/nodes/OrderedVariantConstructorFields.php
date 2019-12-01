<?php

namespace Cthulhu\ir\nodes;

class OrderedVariantConstructorFields extends VariantConstructorFields {
  public array $order;

  /**
   * @param Expr[] $order
   */
  function __construct(array $order) {
    parent::__construct();
    $this->order = $order;
  }

  function children(): array {
    return $this->order;
  }
}
