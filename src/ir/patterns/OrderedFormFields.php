<?php

namespace Cthulhu\ir\patterns;

class OrderedFormFields extends FormFields {
  public array $order;

  public function __construct(array $order) {
    $this->order = $order;
  }

  public function __toString(): string {
    return '(' . implode(', ', $this->order) . ')';
  }
}
