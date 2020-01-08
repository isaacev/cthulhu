<?php

namespace Cthulhu\ir\nodes;

class OrderedVariantConstructorFields extends VariantConstructorFields {
  public array $order;

  /**
   * @param Expr[] $order
   */
  public function __construct(array $order) {
    parent::__construct();
    $this->order = $order;
  }

  public function children(): array {
    return $this->order;
  }
}
