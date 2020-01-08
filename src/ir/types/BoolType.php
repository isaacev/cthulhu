<?php

namespace Cthulhu\ir\types;

class BoolType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  public function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  public function equals(Type $other): bool {
    return $other instanceof BoolType;
  }

  public function __toString(): string {
    return "Bool";
  }
}
