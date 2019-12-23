<?php

namespace Cthulhu\ir\types;

class FloatType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  public function apply_operator(string $op, Type ...$operands): ?Type {
    if (empty($operands)) {
      switch ($op) {
        case '-':
          return new self();
      }
    } else if (count($operands) === 1 && self::matches($operands[0])) {
      switch ($op) {
        case '+':
        case '-':
        case '*':
        case '^':
          return new self();
        case '<':
        case '<=':
        case '>':
        case '>=':
          return new BoolType();
      }
    } else if (count($operands) === 1 && IntType::matches($operands[0])) {
      switch ($op) {
        case '^':
          return new self();
      }
    }

    return parent::apply_operator($op, ...$operands);
  }

  function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  function equals(Type $other): bool {
    return $other instanceof FloatType;
  }

  function __toString(): string {
    return "Float";
  }
}
