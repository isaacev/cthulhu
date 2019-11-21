<?php

namespace Cthulhu\ir\types;

class OrderedConstructorFields extends ConstructorFields {
  public $order;

  /**
   * OrderedConstructorFields constructor.
   * @param Type[] $order
   */
  function __construct(array $order) {
    $this->order = $order;
  }

  function size(): int {
    return count($this->order);
  }

  function get(int $i): ?Type {
    return isset($this->order[$i])
      ? $this->order[$i]
      : null;
  }

  function __toString(): string {
    return '(' . implode(', ', $this->order) . ')';
  }
}
