<?php

namespace Cthulhu\ir\arity;

class UnknownArity extends Arity {
  public function equals(Arity $other): bool {
    return $other instanceof self;
  }

  public function __toString(): string {
    return '?';
  }
}
