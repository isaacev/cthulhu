<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\Arity;
use Cthulhu\ir\nodes\Root;
use Cthulhu\php\Compiler;
use Cthulhu\php\Renamer;

class CodegenPhase {
  private Root $tree;

  public function __construct(Root $tree) {
    $this->tree = $tree;
  }

  public function ir(): Root {
    return $this->tree;
  }

  public function codegen(): LateOptimizationPhase {
    Arity::inspect($this->tree);
    Renamer::rename($this->tree);
    $prog = Compiler::root($this->tree);
    return new LateOptimizationPhase($prog);
  }
}
