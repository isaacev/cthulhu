<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\DefaultUniqueId;
use Cthulhu\lib\trees\Nodelike;

abstract class ShallowNode implements Nodelike {
  use DefaultMetadata;
  use DefaultUniqueId;
}
