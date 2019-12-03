<?php

namespace Cthulhu\ir\types;

class StrType extends Type {
  function apply(string $op, Type ...$operands): ?Type {
    if (count($operands) === 1 && self::matches($operands[0])) {
      switch ($op) {
        case '++':
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
    return 'Str';
  }

  static function matches(Type $other): bool {
    return $other->unwrap() instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
