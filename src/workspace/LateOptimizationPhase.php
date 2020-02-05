<?php

namespace Cthulhu\workspace;

use Cthulhu\php\nodes\Program;
use Cthulhu\php\passes;

class LateOptimizationPhase {
  private const CUTOFF = 3;

  public Program $prog;

  public function __construct(Program $prog) {
    $this->prog = $prog;
  }

  public function optimize(): WritePhase {
    $prev_prog = $this->prog;
    $next_prog = $prev_prog;
    for ($i = 0; $i < self::CUTOFF; $i++) {
      $next_prog = passes\VarReduction::apply($next_prog);

      if ($i === 0) {
        $next_prog = passes\ReturnBackProp::apply($next_prog);
      }

      $next_prog = passes\UnusedExprs::apply($next_prog);

      if ($next_prog === $prev_prog) {
        break;
      } else {
        $prev_prog = $next_prog;
        continue;
      }
    }
    $this->prog = $next_prog;
    $this->prog = passes\TailCall::apply($this->prog);
    return new WritePhase($this->prog);
  }
}
