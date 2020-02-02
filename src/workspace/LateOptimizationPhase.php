<?php

namespace Cthulhu\workspace;

use Cthulhu\php\nodes\Program;

class LateOptimizationPhase {
  public Program $prog;

  public function __construct(Program $prog) {
    $this->prog = $prog;
  }

  public function optimize(): WritePhase {
    return new WritePhase($this->prog);
  }
}
