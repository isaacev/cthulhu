<?php

namespace Cthulhu\php\passes;

use Cthulhu\php\nodes\Program;

interface Pass {
  public static function apply(Program $prog): Program;
}
