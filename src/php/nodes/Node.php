<?php

namespace Cthulhu\php\nodes;

use Cthulhu\ir;
use Cthulhu\lib\fmt;
use Cthulhu\php;

abstract class Node implements fmt\Buildable, ir\HasId {
  use ir\GenerateId;

  public abstract function build(): php\Builder;
}
