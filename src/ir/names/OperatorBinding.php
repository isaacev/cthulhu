<?php

namespace Cthulhu\ir\names;

use Cthulhu\ast\nodes\Operator;

class OperatorBinding extends Binding {
  public Operator $operator;

  public function __construct(Symbol $symbol, bool $is_public, Operator $operator) {
    parent::__construct($operator->value, $symbol, $is_public);
    $this->operator = $operator;
  }

  public function as_private(): self {
    return new self($this->symbol, $this->is_public, $this->operator);
  }
}
