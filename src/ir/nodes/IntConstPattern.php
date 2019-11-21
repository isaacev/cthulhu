<?php

namespace Cthulhu\ir\nodes;

class IntConstPattern extends ConstPattern {
  public $value;

  function __construct(int $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  function __toString(): string {
    return "$this->literal";
  }
}
