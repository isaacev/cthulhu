<?php

namespace Cthulhu\ir\types;

class ListType extends Type {
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  public Type $element;

  function __construct(Type $element) {
    $this->element = $element;
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  function equals(Type $other): bool {
    return (
      $other instanceof ListType &&
      $this->element->equals($other->element)
    );
  }

  function __toString(): string {
    return "[$this->element]";
  }

  /**
   * @return Type[]
   */
  function to_children(): array {
    return [ $this->element ];
  }

  /**
   * @param Type[] $children
   * @return $this
   */
  function from_children(array $children): ListType {
    return new ListType($children[0]);
  }
}
