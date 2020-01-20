<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\nodes2\Root;
use Cthulhu\ir\passes;

class OptimizePhase {
  private Root $tree;

  public function __construct(Root $tree) {
    $this->tree = $tree;
  }

  public function optimize(): CodegenPhase {
    $this->tree = passes\Inline::apply($this->tree);
    $this->tree = passes\ShakeTree::apply($this->tree);
    return new CodegenPhase($this->tree);
  }
}
