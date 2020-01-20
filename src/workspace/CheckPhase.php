<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\nodes\Program;
use Cthulhu\err\Error;
use Cthulhu\ir\Compiler;
use Cthulhu\ir\types\Env;
use Cthulhu\ir\types\hm\TypeSet;
use Cthulhu\ir\types\TypeSolver;
use Cthulhu\types\TypeCompiler;

class CheckPhase {
  private Program $deep;

  public function __construct(Program $deep) {
    $this->deep = $deep;
  }

  /**
   * @return OptimizePhase
   * @throws Error
   */
  public function check(): OptimizePhase {
    $exprs   = (new TypeCompiler($this->deep))->exprs();
    $env     = new Env();
    $non_gen = new TypeSet();

    foreach ($exprs as $expr) {
      TypeSolver::expr($expr, $env, $non_gen);
    }

    $ir = Compiler::program($this->deep, $env);
    return new OptimizePhase($ir);
  }
}
