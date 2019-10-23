<?php

namespace Cthulhu\ir\nodes;

class BoolLiteral extends Literal {
  public $value;

  function __construct(bool $value) {
    parent::__construct();
    $this->value = $value;
  }

  function children(): array {
    return [];
  }
}
