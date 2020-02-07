<?php

namespace Cthulhu\ir\nodes;

class WildcardPattern extends Pattern {
  public function children(): array {
    return [];
  }

  public function from_children(array $children): WildcardPattern {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('_');
  }

  public function __toString(): string {
    return '_';
  }
}
