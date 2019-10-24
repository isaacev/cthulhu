<?php

namespace Cthulhu\ir\types;

class BoolType extends Type {
  function accepts(Type $other): bool {
    return self::matches($other);
  }

  function replace_generics(array $replacements): Type {
    return $this;
  }

  function __toString(): string {
    return 'Bool';
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
