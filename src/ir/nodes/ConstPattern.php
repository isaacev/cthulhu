<?php

namespace Cthulhu\ir\nodes;

abstract class ConstPattern extends Pattern {
  public function children(): array {
    return [];
  }

  public function from_children(array $children): ConstPattern {
    return $this;
  }
}
