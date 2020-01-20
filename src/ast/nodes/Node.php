<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\DefaultUniqueId;
use Cthulhu\lib\trees\HasMetadata;
use Cthulhu\lib\trees\Nodelike;

abstract class Node implements Nodelike, HasMetadata {
  use DefaultMetadata;
  use DefaultUniqueId;
}
