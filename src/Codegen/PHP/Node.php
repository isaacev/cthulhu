<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;

abstract class Node implements Buildable {
  public function to_children(): array {
    return [];
  }

  public function from_children(array $nodes): self {
    return $this;
  }
}
