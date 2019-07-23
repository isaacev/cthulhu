<?php

namespace Cthulhu\Types;

abstract class Type implements \JsonSerializable {
  public abstract function accepts(Type $other): bool;
  public abstract function __toString(): string;
}
