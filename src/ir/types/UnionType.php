<?php

namespace Cthulhu\ir\types;

class UnionType extends Type {
  public $name;
  public $variants;

  /**
   * UnionType constructor.
   * @param string $name
   * @param VariantFields[] $variants
   */
  function __construct(string $name, array $variants) {
    $this->name = $name;
    $this->variants = $variants;
  }

  function has_variant_named(string $name): bool {
    return isset($this->variants[$name]);
  }

  function get_variant_fields(string $name): VariantFields {
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

  function to_variant_string(): string {
    $variants = [];
    foreach ($this->variants as $name => $fields) {
      $variants[] = "$name$fields";
    }
    return implode(' | ', $variants);
  }

  function __toString(): string {
    return $this->name;
  }

  static function from_array(string $name, array $variants): self {
    $new_variants = [];
    foreach ($variants as $name => $fields) {
      if ($fields === null) {
        $new_variants[$name] = new UnitVariantFields();
      } else if (array_keys($fields) !== range(0, count($fields) - 1)) {
        $new_variants[$name] = new NamedVariantFields($fields);
      } else {
        $new_variants[$name] = new OrderedVariantFields($fields);
      }
    }
    return new self($name, $new_variants);
  }
}
