<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\nodes\Root;

class CodegenPhase {
  private Root $tree;

  public function __construct(Root $tree) {
    $this->tree = $tree;
  }

  public function codegen(): WritePhase {
    // TODO
  }
}
