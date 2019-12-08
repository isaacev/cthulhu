<?php

namespace Cthulhu\workspace;

use Cthulhu\Errors\Error;
use Cthulhu\ir\names\Resolve;
use Cthulhu\ir\nodes\Program;

class ResolvePhase {
  private Program $ir_tree;

  function __construct(Program $ir_tree) {
    $this->ir_tree = $ir_tree;
  }

  /**
   * @return CheckPhase
   * @throws Error
   */
  function resolve(): CheckPhase {
    Resolve::names($this->ir_tree);
    Resolve::validate($this->ir_tree);
    return new CheckPhase($this->ir_tree);
  }
}
