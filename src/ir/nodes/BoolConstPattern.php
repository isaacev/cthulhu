<?php

namespace Cthulhu\ir\nodes;

class BoolConstPattern extends ConstPattern {
  public bool $value;

  function __construct(bool $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  function __toString(): string {
    return $this->value ? 'true' : 'false';
  }
}
