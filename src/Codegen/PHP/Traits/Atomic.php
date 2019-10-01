<?php

namespace Cthulhu\Codegen\PHP\Traits;

use Cthulhu\Codegen\PHP;

trait Atomic {
  public function to_children(): array {
    return [];
  }

  public function from_children(array $nodes): PHP\Node {
    return $this;
  }
}
