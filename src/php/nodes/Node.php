<?php

namespace Cthulhu\php\nodes;

use Cthulhu\ir;
use Cthulhu\php\Buildable;

abstract class Node implements Buildable, ir\HasId {
  use ir\GenerateId;

  public abstract function to_children(): array;

  public abstract function from_children(array $nodes): self;
}
