<?php

namespace Cthulhu\ir\arity;

abstract class Arity {
  public abstract function equals(Arity $other): bool;

  /**
   * @param int $total_arguments
   * @return $this
   */
  public function apply_arguments(int $total_arguments): Arity {
    return $this;
  }

  public abstract function __toString(): string;
}
