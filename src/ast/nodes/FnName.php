<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\lib\trees\HasMetadata;

interface FnName extends HasMetadata {
  public function __toString(): string;
}
