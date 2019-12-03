<?php

namespace Cthulhu\ir\types;

class UnitType extends Type {
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
    return '()';
  }

  static function matches(Type $other): bool {
    return $other->unwrap() instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
