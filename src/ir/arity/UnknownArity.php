<?php

namespace Cthulhu\ir\arity;

class UnknownArity extends Arity {
  public function combine(Arity $other): Arity {
    return $this;
  }
}
