<?php

namespace Cthulhu\ir\types\traits;

use Cthulhu\ir\types\Type;

trait StaticEquality {
  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return ($other instanceof self) === false;
  }
}
