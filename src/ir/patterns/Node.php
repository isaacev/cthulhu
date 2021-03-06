<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types;
use Cthulhu\lib\panic\Panic;

abstract class Node {
  abstract public function is_covered(): bool;

  abstract public function is_redundant(Pattern $pattern): bool;

  abstract public function apply(Pattern $pattern): void;

  /**
   * @return Pattern[]
   */
  abstract public function uncovered_patterns(): array;

  public static function from_type(types\Type $type): self {
    $type = $type->flatten();

    if ($type instanceof types\Enum) {
      return new EnumNode($type);
    }

    if ($type instanceof types\FixedTypeVar || $type instanceof types\FreeTypeVar) {
      return new ParamNode();
    }

    if ($type instanceof types\ListType) {
      return new ListNode($type);
    }

    if ($type instanceof types\Atomic) {
      switch ($type->name) {
        case 'Str':
          return new StrNode();
        case 'Float':
          return new FloatNode();
        case 'Int':
          return new IntNode();
        case 'Bool':
          return new BoolNode();
        default:
          Panic::if_reached(__LINE__, __FILE__);
      }
    }

    Panic::if_reached(__LINE__, __FILE__);
  }
}
