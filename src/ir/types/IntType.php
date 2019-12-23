<?php

namespace Cthulhu\ir\types;

class IntType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  function equals(Type $other): bool {
    return $other instanceof IntType;
  }

  public function apply_operator(string $op, Type ...$operands): ?Type {
    if (empty($operands)) {
      switch ($op) {
        case '-':
          return new IntType();
      }
    } else if (count($operands) === 1 && IntType::matches($operands[0])) {
      switch ($op) {
        case '+':
        case '-':
        case '*':
        case '^':
          return new IntType();
        case '==':
        case '<':
        case '<=':
        case '>':
        case '>=':
          return new BoolType();
      }
    } else if (count($operands) === 1 && FloatType::matches($operands[0])) {
      switch ($op) {
        case '^':
          return new FloatType();
      }
    }

    return parent::apply_operator($op, ...$operands);
  }

  function __toString(): string {
    return "Int";
  }
}
