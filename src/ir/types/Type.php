<?php

namespace Cthulhu\ir\types;

abstract class Type {
  function apply(string $op, self ...$operands): ?Type {
    return null;
  }

  abstract function accepts_as_parameter(self $other): bool;

  function accepts_as_return(self $other): bool {
    return $this->accepts_as_parameter($other);
  }

  abstract function unify(self $other): ?self;

  abstract function __toString(): string;
}
