<?php

namespace Cthulhu\ir\types;

class NumType extends Type {
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

  function equals(Type $other): bool {
    return self::matches($other);
  }

  function __toString(): string {
    return 'Num';
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
