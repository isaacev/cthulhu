<?php

namespace Cthulhu\ast\nodes;

class VariablePattern extends Pattern {
  public LowerName $name;

  public function __construct(LowerName $name) {
    parent::__construct();
    $this->name = $name;
  }

  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return "$this->name";
  }
}
