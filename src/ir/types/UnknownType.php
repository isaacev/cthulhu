<?php

namespace Cthulhu\ir\types;

class UnknownType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  function equals(Type $other): bool {
    return $other instanceof self;
  }

  function __toString(): string {
    return "_";
  }
}
