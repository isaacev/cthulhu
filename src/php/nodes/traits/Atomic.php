<?php

namespace Cthulhu\php\nodes\traits;

use Cthulhu\php\nodes;

trait Atomic {
  public function to_children(): array {
    return [];
  }

  public function from_children(array $nodes): nodes\Node {
    return $this;
  }
}
