<?php

namespace Cthulhu\ir\types;

class FloatType extends Type {
  function apply(string $op, Type ...$operands): ?Type {
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

    return parent::apply($op, ...$operands);
  }

  function accepts_as_parameter(Type $other): bool {
    return self::matches($other);
  }

  function unify(Type $other): ?Type {
    if (self::matches($other)) {
      return new self();
    }
    return null;
  }

  function __toString(): string {
    return 'Float';
  }

  static function matches(Type $other): bool {
    return $other->unwrap() instanceof self;
  }
}
