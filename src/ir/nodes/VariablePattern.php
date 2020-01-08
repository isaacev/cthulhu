<?php

namespace Cthulhu\ir\nodes;

class VariablePattern extends Pattern {
  public Name $name;

  public function __construct(Name $name) {
    parent::__construct();
    $this->name = $name;
  }

  public function children(): array {
    return [ $this->name ];
  }

  public function __toString(): string {
    return $this->name;
  }
}
