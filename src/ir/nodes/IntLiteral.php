<?php

namespace Cthulhu\ir\nodes;

class IntLiteral extends Literal {
  public int $value;

  function __construct(int $value) {
    parent::__construct();
    $this->value = $value;
  }

  function children(): array {
    return [];
  }
}
