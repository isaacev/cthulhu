<?php

namespace Cthulhu\ir\types;

class UnionType extends Type {
  public $name;
  public $variants;

  /**
   * UnionType constructor.
   * @param string $name
   * @param array[string]Type $variants
   */
  function __construct(string $name, array $variants) {
    $this->name = $name;
    $this->variants = $variants;
  }

  function has_variant_named(string $name): bool {
    return isset($this->variants[$name]);
  }

  function get_variant_arguments(string $name): Type {
    return $this->variants[$name];
  }

  function accepts_as_parameter(Type $other): bool {
    return $this === $other;
  }

  function unify(Type $other): ?Type {
    if ($this === $other) {
      return $this;
    }
    return null;
  }

  function __toString(): string {
    $variants = [];
    foreach ($this->variants as $name => $type) {
      if ($type instanceof UnitType) {
        $variants[] = $name;
      } else if ($type instanceof RecordType || $type instanceof TupleType) {
        $variants[] = $name . "$type";
      } else {
        $variants[] = $name . "($type)";
      }
    }
    return "$this->name = " . implode(' | ', $variants);
  }
}
