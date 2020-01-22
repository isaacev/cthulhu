<?php

namespace Cthulhu\php\nodes\traits;

use Cthulhu\php\nodes;

trait Unary {
  public function children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): nodes\Node {
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return new self($nodes[0]);
  }
}
