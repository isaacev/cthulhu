<?php

namespace Cthulhu\ir\arity;

class UnknownArity extends Arity {
  public function __toString(): string {
    return '?';
  }
}
