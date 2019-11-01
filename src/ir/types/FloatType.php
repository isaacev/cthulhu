<?php

namespace Cthulhu\ir\types;

class FloatType extends Type {
  function apply(string $op, Type ...$operands): ?Type {
    if (empty($operands)) {
      switch ($op) {
        case '-':
          return new self();
      }
    } else if (count($operands) === 1 && $operands[0] instanceof self) {
      switch ($op) {
        case '+':
        case '-':
        case '*':
          return new self();
        case '<':
        case '<=':
        case '>':
        case '>=':
          return new BoolType();
      }
    }

    return parent::apply($op, ...$operands);
  }

  function accepts_as_parameter(Type $other): bool {
    return $other instanceof self;
  }

  function unify(Type $other): ?Type {
    if ($other instanceof self) {
      return new self();
    }
    return null;
  }

  function __toString(): string {
    return 'Float';
  }
}
