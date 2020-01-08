<?php

namespace Cthulhu\ir\types;

interface Walkable {
  public function similar_to(Walkable $other): bool;

  public function transform(callable $fn): ?Walkable;

  public function compare(Walkable $other, callable $fn): void;

  public function compare_and_transform(Walkable $other, callable $fn): ?Walkable;
}
