<?php

namespace Cthulhu\ir\patterns;

class OrderedVariantFields extends VariantFields {
  public array $order;

  function __construct(array $order) {
    $this->order = $order;
  }

  function __toString(): string {
    return '(' . implode(', ', $this->order) . ')';
  }
}
