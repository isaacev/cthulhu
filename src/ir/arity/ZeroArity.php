<?php

namespace Cthulhu\ir\arity;

class ZeroArity extends KnownArity {
  public function __construct() {
    parent::__construct(0, $this);
  }

  public function equals(Arity $other): bool {
    return $other instanceof self;
  }

  public function __toString(): string {
    return '0';
  }
}
