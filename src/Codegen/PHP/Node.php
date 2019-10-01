<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;

abstract class Node implements Buildable {
  public abstract function to_children(): array;
  public abstract function from_children(array $nodes): self;
}
