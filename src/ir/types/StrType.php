<?php

namespace Cthulhu\ir\types;

class StrType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  function equals(Type $other): bool {
    return $other instanceof StrType;
  }

  function apply_operator(string $op, Type ...$operands): ?Type {
    if (count($operands) === 1 && StrType::matches($operands[0])) {
      switch ($op) {
        case '++':
          return new StrType();
      }
    }

    return parent::apply_operator($op, ...$operands);
  }

  function __toString(): string {
    return "Str";
  }
}
