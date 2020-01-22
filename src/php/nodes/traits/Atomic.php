<?php

namespace Cthulhu\php\nodes\traits;

use Cthulhu\php\nodes;

trait Atomic {
  public function children(): array {
    return [];
  }

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public function from_children(array $nodes): nodes\Node {
    return $this;
  }
}
