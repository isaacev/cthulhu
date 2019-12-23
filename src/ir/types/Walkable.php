<?php

namespace Cthulhu\ir\types;

interface Walkable {
  function similar_to(Walkable $other): bool;

  function transform(callable $fn): ?Walkable;

  function compare(Walkable $other, callable $fn): void;

  function compare_and_transform(Walkable $other, callable $fn): ?Walkable;
}
