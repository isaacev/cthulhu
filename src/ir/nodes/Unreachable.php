<?php

namespace Cthulhu\ir\nodes;

class Unreachable extends Expr {
  public function children(): array {
    return [];
  }

  public function from_children(array $children): Node {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('unreachable');
  }
}
