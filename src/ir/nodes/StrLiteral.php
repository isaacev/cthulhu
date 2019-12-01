<?php

namespace Cthulhu\ir\nodes;

class StrLiteral extends Literal {
  public string $value;

  function __construct(string $value) {
    parent::__construct();
    $this->value = $value;
  }

  function children(): array {
    return [];
  }
}
