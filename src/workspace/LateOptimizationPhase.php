<?php

namespace Cthulhu\workspace;

use Cthulhu\php\nodes\Program;
use Cthulhu\php\passes;

class LateOptimizationPhase {
  public Program $prog;

  public function __construct(Program $prog) {
    $this->prog = $prog;
  }

  public function optimize(): WritePhase {
    $this->prog = passes\VarReduction::apply($this->prog);
    $this->prog = passes\ReturnBackProp::apply($this->prog);
    $this->prog = passes\UnusedExprs::apply($this->prog);
    $this->prog = passes\TailCall::apply($this->prog);
    return new WritePhase($this->prog);
  }
}
