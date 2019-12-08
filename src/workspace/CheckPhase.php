<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\Flow;
use Cthulhu\ir\nodes\Program;
use Cthulhu\ir\types\Check;

class CheckPhase {
  private Program $ir_tree;

  function __construct(Program $ir_tree) {
    $this->ir_tree = $ir_tree;
  }

  function check(): CodegenPhase {
    Check::types($this->ir_tree);
    Flow::analyze($this->ir_tree);
    return new CodegenPhase($this->ir_tree);
  }
}
