<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\nodes\Program;
use Cthulhu\err\Error;
use Cthulhu\ir\Compiler;
use Cthulhu\ir\names\Binding;
use Cthulhu\ir\TypeCheck;

class CheckPhase {
  private array $types;
  private Program $deep;

  /**
   * @param Binding[] $types
   * @param Program   $deep
   */
  public function __construct(array $types, Program $deep) {
    $this->types = $types;
    $this->deep  = $deep;
  }

  /**
   * @return OptimizePhase
   * @throws Error
   */
  public function check(): OptimizePhase {
    TypeCheck::syntax_tree($this->types, $this->deep);
    $ir = Compiler::program($this->deep);
    return new OptimizePhase($ir);
  }
}
