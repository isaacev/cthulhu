<?php

namespace Cthulhu\ir\arity;

class ZeroArity extends Arity {
  public function combine(Arity $other): Arity {
    if ($other instanceof UnknownArity) {
      return $other;
    }
    assert($other instanceof ZeroArity);
    return $this;
  }
}
