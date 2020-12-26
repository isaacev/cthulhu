<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes\Root;

interface Pass {
  public static function apply(Root $root): Root;
}
