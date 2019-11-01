<?php

namespace Cthulhu\ir\types;

class IntType extends Type {
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
    } else if (count($operands) === 1 && $operands[0] instanceof FloatType) {
      switch ($op) {
        case '^':
          return new FloatType();
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
    return 'Int';
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
