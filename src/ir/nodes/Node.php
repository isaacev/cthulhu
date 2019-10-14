<?php

namespace Cthulhu\ir\nodes;

abstract class Node implements \Cthulhu\ir\HasId {
  use \Cthulhu\ir\GenerateId;

  abstract function children(): array;
}
