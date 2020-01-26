<?php

namespace Cthulhu\ir\arity;

abstract class Arity {
  public function apply(int $total_args): Arity {
    return $this;
  }

  abstract public function __toString(): string;
}
