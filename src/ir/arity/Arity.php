<?php

namespace Cthulhu\ir\arity;

abstract class Arity {
  abstract function equals(Arity $other): bool;

  /**
   * @param int $total_arguments
   * @return $this
   */
  function apply_arguments(int $total_arguments): Arity {
    return $this;
  }

  abstract function __toString(): string;
}
