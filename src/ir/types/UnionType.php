<?php

namespace Cthulhu\ir\types;

class UnionType extends Type {
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  public array $variants;

  /**
   * @param Variant[] $variants
   */
  public function __construct(array $variants) {
    $this->variants = $variants;
  }

  public function has_variant_named(string $name): bool {
    return array_key_exists($name, $this->variants);
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof UnionType;
  }

  public function equals(Type $other): bool {
    if (
      $other instanceof UnionType &&
      count($this->variants) === count($other->variants)
    ) {
      foreach ($this->variants as $name => $variant) {
        if (
          array_key_exists($name, $other->variants) === false ||
          $variant->equals($other->variants[$name]) === false) {
          return false;
        }
      }
      return true;
    }
    return false;
  }

  public function to_children(): array {
    return $this->variants;
  }

  public function from_children(array $children): self {
    return new UnionType($children);
  }

  public function __toString(): string {
    $out = "";
    foreach ($this->variants as $name => $variant) {
      if (!empty($out)) {
        $out .= " | ";
      }
      $out .= "$name$variant";
    }
    return $out;
  }
}
