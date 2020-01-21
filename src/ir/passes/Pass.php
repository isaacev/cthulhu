<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes2\Root;

interface Pass {
  public static function apply(Root $root): Root;
}
