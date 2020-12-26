<?php

namespace Cthulhu\ir\passes\inline;

use Cthulhu\ir\nodes\Root;
use Cthulhu\ir\passes\Pass;
use Cthulhu\lib\trees\Visitor;

class Inline implements Pass {
  public static function apply(Root $root): Root {
    Visitor::walk2($root, new FindCandidates());
  }
}
