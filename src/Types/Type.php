<?php

namespace Cthulhu\Types;

abstract class Type implements \JsonSerializable {
  public abstract function accepts(Type $other): bool;
  public abstract function __toString(): string;

  public function binary_operator(string $operator, Type $right): Type {
    throw new Errors\UnsupportedOperator($this, $operator, $right);
  }

  public function member(string $property): Type {
    throw new Errors\UnsupportedMember($this, $property);
  }
}
