<?php

namespace Cthulhu\ir\types;

abstract class Type {
  public function prune(): Type {
    return $this;
  }

  abstract public function flatten(): Type;

  abstract public function contains(Type $other): bool;

  abstract public function fresh(ParameterContext $ctx): Type;

  abstract public function __toString(): string;
}
