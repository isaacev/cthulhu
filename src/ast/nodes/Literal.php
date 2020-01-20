<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\val\Value;

abstract class Literal extends Expr {
  public Value $value;

  public function __construct(Value $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }
}
