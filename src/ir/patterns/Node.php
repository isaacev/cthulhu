<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types;

abstract class Node {
  abstract function is_covered(): bool;

  abstract function is_redundant(Pattern $pattern): bool;

  abstract function apply(Pattern $pattern): void;

  abstract function uncovered_patterns(): array;

  static function from_type(types\Type $type): self {
    switch (true) {
      case $type instanceof types\NamedType:
        return self::from_type($type->pointer);
      case $type instanceof types\UnionType:
        return new UnionNode($type);
      case $type instanceof types\StrType:
        return new StrNode();
      case $type instanceof types\FloatType:
        return new FloatNode();
      case $type instanceof types\IntType:
        return new IntNode();
      case $type instanceof types\BoolType:
        return new BoolNode();
      case $type instanceof types\FreeType:
      case $type instanceof types\FixedType:
        return new ParamNode();
      default:
        die("unreachable");
    }
  }
}
