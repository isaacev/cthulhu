<?php

namespace Cthulhu\ir\arity;

class ZeroArity extends Arity {
  public function __toString(): string {
    return '0';
  }
}
