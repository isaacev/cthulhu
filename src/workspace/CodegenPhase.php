<?php

namespace Cthulhu\workspace;

use Cthulhu\err\Error;
use Cthulhu\ir\nodes\Program;
use Cthulhu\php\Lower;

class CodegenPhase {
  private Program $ir_tree;

  public function __construct(Program $ir_tree) {
    $this->ir_tree = $ir_tree;
  }

  /**
   * @return OptimizePhase
   * @throws Error
   */
  public function codegen(): OptimizePhase {
    $php_tree = Lower::from($this->ir_tree);
    return new OptimizePhase($php_tree);
  }
}
