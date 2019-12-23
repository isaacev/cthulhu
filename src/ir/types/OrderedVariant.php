<?php

namespace Cthulhu\ir\types;

class OrderedVariant extends Variant {
  use traits\DefaultWalkable;

  public array $order;

  /**
   * @param Type[] $order
   */
  public function __construct(array $order) {
    $this->order = $order;
  }

  /**
   * @param Type[] $arguments
   * @return FreeType[]
   */
  public function infer_free_types(array $arguments): array {
    $inference = [];

    assert(count($this) === count($arguments));
    foreach ($this->order as $index => $type) {
      $arg_type = $arguments[$index];
      // TODO
    }

    return $inference;
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof OrderedVariant;
  }

  public function equals(Variant $other): bool {
    if ($other instanceof OrderedVariant && count($this) === count($other)) {
      for ($i = 0; $i < count($this); $i++) {
        $this_child  = $this->order[$i];
        $other_child = $other->order[$i];
        if ($this_child->equals($other_child) === false) {
          return false;
        }
      }
      return true;
    }
    return false;
  }

  function to_children(): array {
    return $this->order;
  }

  function from_children(array $children): self {
    return new OrderedVariant($children);
  }

  public function count(): int {
    return count($this->order);
  }

  public function __toString(): string {
    if (empty($this->order)) {
      return "()";
    }
    return "(" . implode(", ", $this->order) . ")";
  }
}
