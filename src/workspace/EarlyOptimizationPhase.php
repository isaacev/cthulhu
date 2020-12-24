<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\nodes\Root;
use Cthulhu\ir\passes;

class EarlyOptimizationPhase {
  private Root $tree;

  public function __construct(Root $tree) {
    $this->tree = $tree;
  }

  /**
   * @param string[] $skip
   * @return CodegenPhase
   */
  public function optimize(array $skip): CodegenPhase {
    $this->tree = passes\Inline::apply($this->tree, $skip);
    $this->tree = passes\ShakeTree::apply($this->tree, $skip);
    $this->tree = passes\CombineCalls::apply($this->tree, $skip);
    return new CodegenPhase($this->tree);
  }
}
