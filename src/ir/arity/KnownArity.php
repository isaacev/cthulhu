<?php

namespace Cthulhu\ir\arity;

abstract class KnownArity extends Arity {
  public int $params;
  public Arity $returns;

  function __construct(int $params, Arity $returns) {
    $this->params  = $params;
    $this->returns = $returns;
  }

  public function apply_arguments(int $total_arguments): Arity {
    $leftover = $this->params - $total_arguments;
    if ($leftover > 0) {
      return new DynamicArity($leftover, $this->returns);
    } else if ($leftover === 0) {
      return $this->returns;
    } else {
      return $this->returns->apply_arguments(abs($leftover));
    }
  }
}
