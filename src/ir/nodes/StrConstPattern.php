<?php

namespace Cthulhu\ir\nodes;

class StrConstPattern extends ConstPattern {
  public string $value;

  function __construct(string $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  function __toString(): string {
    return '"' . $this->value  . '"';
  }
}
