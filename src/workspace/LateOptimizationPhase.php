<?php

namespace Cthulhu\workspace;

use Cthulhu\php\nodes\Program;
use Cthulhu\php\passes\VarReduction;

class LateOptimizationPhase {
  public Program $prog;

  public function __construct(Program $prog) {
    $this->prog = $prog;
  }

  public function optimize(): WritePhase {
    $this->prog = VarReduction::apply($this->prog);
    return new WritePhase($this->prog);
  }
}
