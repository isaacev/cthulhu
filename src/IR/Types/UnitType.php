<?php

namespace Cthulhu\IR\Types;

class UnitType extends Type {
  function equals(Type $other): bool {
    return self::is_equal_to($other);
  }

  function __toString(): string {
    return '()';
  }

  static function is_equal_to(Type $other): bool {
    return $other instanceof self;
  }

  static function not_equal_to(Type $other): bool {
    return self::is_equal_to($other) === false;
  }
}
