<?php

namespace Cthulhu\ir\names;

use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\DefaultUniqueId;
use Cthulhu\lib\trees\HasMetadata;

abstract class Symbol implements HasMetadata {
  public const KEY = 'symbol';

  use DefaultMetadata;
  use DefaultUniqueId;

  abstract public function __toString(): string;
}
