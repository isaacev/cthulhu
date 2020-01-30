<?php

namespace Cthulhu\ir\arity;

abstract class Arity {
  abstract public function combine(Arity $other): Arity;

  public function apply(int $total_args): Arity {
    return $this;
  }
}
