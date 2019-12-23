<?php

namespace Cthulhu\ir\types;

class BoolType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  function equals(Type $other): bool {
    return $other instanceof BoolType;
  }

  function __toString(): string {
    return "Bool";
  }
}
