<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\nodes\Program;
use Cthulhu\php\Lower;

class CodegenPhase {
  private Program $ir_tree;

  function __construct(Program $ir_tree) {
    $this->ir_tree = $ir_tree;
  }

  function codegen(): OptimizePhase {
    $php_tree = Lower::from($this->ir_tree);
    return new OptimizePhase($php_tree);
  }
}
