<?php

namespace Cthulhu\ir\types;

class UnitVariant extends Variant {
  use traits\NoChildren;
  use traits\DefaultWalkable;

  /**
   * @param Type[] $arguments
   * @return Type[]
   */
  public function infer_free_types(array $arguments): array {
    return [];
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  public function equals(Variant $other): bool {
    return $other instanceof UnitVariant;
  }

  public function count(): int {
    return 0;
  }

  public function __toString(): string {
    return "";
  }
}
