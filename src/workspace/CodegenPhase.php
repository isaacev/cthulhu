<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\Arity;
use Cthulhu\ir\nodes\Root;
use Cthulhu\php\Compiler;

class CodegenPhase {
  private Root $tree;

  public function __construct(Root $tree) {
    $this->tree = $tree;
  }

  public function codegen(): LateOptimizationPhase {
    Arity::inspect($this->tree);
    $prog = Compiler::root($this->tree);
    return new LateOptimizationPhase($prog);
  }
}
