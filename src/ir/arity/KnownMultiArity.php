<?php

namespace Cthulhu\ir\arity;

class KnownMultiArity extends Arity {
  public int $params;
  public Arity $returns;

  public function __construct(int $params, Arity $returns) {
    $this->params  = $params;
    $this->returns = $returns;
  }

  public function apply(int $total_args): Arity {
    $leftover = $this->params - $total_args;
    if ($leftover > 0) {
      return new KnownMultiArity($leftover, $this->returns);
    } else if ($leftover === 0) {
      return $this->returns;
    } else {
      return $this->returns->apply(abs($leftover));
    }
  }

  public function __toString(): string {
    return "known($this->params) -> $this->returns";
  }
}
