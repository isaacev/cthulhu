<?php

namespace Cthulhu\ir\nodes;

class NullaryFormPattern extends FormPattern {
  public function children(): array {
    return [];
  }

  public function from_children(array $children): NullaryFormPattern {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword("$this->ref_symbol");
  }

  public function __toString(): string {
    return "$this->ref_symbol";
  }
}
