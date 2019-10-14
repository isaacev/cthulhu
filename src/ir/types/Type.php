<?php

namespace Cthulhu\ir\types;

abstract class Type {
  function apply(string $op, self ...$operands): ?Type {
    return null;
  }

  abstract function equals(self $other): bool;

  abstract function __toString(): string;
}
