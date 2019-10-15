<?php

namespace Cthulhu\php\nodes\traits;

use Cthulhu\php\nodes;

trait Unary {
  public function to_children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): nodes\Node {
    return new self($nodes[0]);
  }
}