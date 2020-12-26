<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\nodes\Root;
use Cthulhu\ir\passes;

class EarlyOptimizationPhase {
  private Root $tree;

  public function __construct(Root $tree) {
    $this->tree = $tree;
  }

  public function optimize(): CodegenPhase {
    $this->tree = passes\Inline::apply($this->tree);
    $this->tree = passes\ShakeTree::apply($this->tree);
    $this->tree = passes\CombineCalls::apply($this->tree);
    return new CodegenPhase($this->tree);
  }
}
