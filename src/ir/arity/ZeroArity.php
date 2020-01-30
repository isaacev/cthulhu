<?php

namespace Cthulhu\ir\arity;

class ZeroArity extends Arity {
  public function combine(Arity $other): Arity {
    assert($other instanceof ZeroArity);
    return $this;
  }
}
