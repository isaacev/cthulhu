<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\lib\fmt\Buildable;
use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\HasMetadata;

abstract class Expr implements HasMetadata, Buildable {
  use DefaultMetadata;
}
