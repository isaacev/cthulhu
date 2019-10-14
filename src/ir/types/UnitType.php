<?php

namespace Cthulhu\ir\types;

class UnitType extends Type {
  function equals(Type $other): bool {
    return self::matches($other);
  }

  function __toString(): string {
    return '()';
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
