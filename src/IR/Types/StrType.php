<?php

namespace Cthulhu\IR\Types;

class StrType extends Type {
  function equals(Type $other): bool {
    return self::is_equal_to($other);
  }

  function __toString(): string {
    return 'Str';
  }

  static function is_equal_to(Type $other): bool {
    return $other instanceof self;
  }

  static function not_equal_to(Type $other): bool {
    return self::is_equal_to($other) === false;
  }
}
