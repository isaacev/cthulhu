<?php

namespace Cthulhu\ir\types;

use Countable;

abstract class Variant implements Countable, Walkable {
  /**
   * @param Type[] $arguments
   * @return Type[]
   */
  abstract public function infer_free_types(array $arguments): array;

  abstract public function equals(Variant $other): bool;

  abstract public function __toString(): string;
}
