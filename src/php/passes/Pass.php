<?php

namespace Cthulhu\php\passes;

use Cthulhu\php\nodes\Program;

interface Pass {
  /**
   * @param Program  $prog
   * @param string[] $skip
   * @return Program
   */
  public static function apply(Program $prog, array $skip): Program;
}
