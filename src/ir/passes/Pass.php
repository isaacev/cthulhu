<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes\Root;

interface Pass {
  /**
   * @param Root     $root
   * @param string[] $skip
   * @return Root
   */
  public static function apply(Root $root, array $skip): Root;
}
