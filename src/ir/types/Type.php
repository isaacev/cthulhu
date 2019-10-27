<?php

namespace Cthulhu\ir\types;

abstract class Type {
  function apply(string $op, self ...$operands): ?Type {
    return null;
  }

  abstract function accepts(self $other): bool;

  abstract function unify(self $other): ?self;

  abstract function replace_generics(array $replacements): Type;

  abstract function __toString(): string;
}
