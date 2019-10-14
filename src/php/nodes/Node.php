<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Buildable;

abstract class Node implements Buildable, \Cthulhu\ir\HasId {
  use \Cthulhu\ir\GenerateId;

  public abstract function to_children(): array;
  public abstract function from_children(array $nodes): self;
}
