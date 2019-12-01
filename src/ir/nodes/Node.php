<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir;

abstract class Node implements ir\HasId {
  use ir\GenerateId;

  abstract function children(): array;
}
