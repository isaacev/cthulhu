<?php

namespace Cthulhu\Codegen\PHP\Traits;

use Cthulhu\Codegen\PHP;

trait Unary {
  public function to_children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): PHP\Node {
    return new self($nodes[0]);
  }
}
