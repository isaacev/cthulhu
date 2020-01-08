<?php

namespace Cthulhu\ir\types;

class NamedVariant extends Variant {
  use traits\DefaultWalkable;

  public array $mapping;

  /**
   * @param Type[] $mapping
   */
  public function __construct(array $mapping) {
    $this->mapping = $mapping;
  }

  /**
   * @param Type[] $arguments
   * @return Type[]
   */
  public function infer_free_types(array $arguments): array {
    $inference = [];

    assert(count($this) === count($arguments));
    foreach ($this->mapping as $name => $type) {
      assert(array_key_exists($name, $arguments));
      $arg_type = $arguments[$name];
      // TODO
    }

    return $inference;
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof NamedVariant;
  }

  public function equals(Variant $other): bool {
    if ($other instanceof NamedVariant && count($this) === count($other)) {
      foreach ($this->mapping as $name => $this_child) {
        $other_child = $other->mapping[$name];
        if ($this_child->equals($other_child) === false) {
          return false;
        }
      }
      return true;
    }
    return false;
  }

  public function to_children(): array {
    return $this->mapping;
  }

  public function from_children(array $children): self {
    return new NamedVariant($children);
  }

  public function count(): int {
    return count($this->mapping);
  }

  public function __toString(): string {
    $out = "";
    foreach ($this->mapping as $name => $type) {
      if (!empty($out)) {
        $out .= ",";
      }
      $out .= " $type";
    }
    return " {$out }";
  }
}
