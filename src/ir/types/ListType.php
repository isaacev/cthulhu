<?php

namespace Cthulhu\ir\types;

class ListType extends Type {
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  public Type $element;

  public function __construct(Type $element) {
    $this->element = $element;
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  public function equals(Type $other): bool {
    return (
      $other instanceof ListType &&
      $this->element->equals($other->element)
    );
  }

  public function __toString(): string {
    return "[$this->element]";
  }

  /**
   * @return Type[]
   */
  public function to_children(): array {
    return [ $this->element ];
  }

  /**
   * @param Type[] $children
   * @return $this
   */
  public function from_children(array $children): ListType {
    return new ListType($children[0]);
  }
}
