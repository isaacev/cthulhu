<?php

namespace Cthulhu\workspace;

use Cthulhu\err\Error;
use Cthulhu\ir\Arity;
use Cthulhu\ir\Flow;
use Cthulhu\ir\nodes\Program;
use Cthulhu\ir\types\Check;

class CheckPhase {
  private Program $ir_tree;

  public function __construct(Program $ir_tree) {
    $this->ir_tree = $ir_tree;
  }

  /**
   * @return CodegenPhase
   * @throws Error
   */
  public function check(): CodegenPhase {
    Check::types($this->ir_tree);
    Check::validate($this->ir_tree);
    Flow::analyze($this->ir_tree);
    Arity::analyze($this->ir_tree);
    Arity::validate($this->ir_tree);
    return new CodegenPhase($this->ir_tree);
  }
}
