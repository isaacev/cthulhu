<?php

namespace Cthulhu\lib\trees;

class Path {
  public ?Path $parent;
  public Nodelike $node;

  public function __construct(?self $parent, Nodelike $node) {
    $this->parent = $parent;
    $this->node   = $node;
  }

  public function extend(Nodelike $node): self {
    return new self($this, $node);
  }

  public function has_parent(string $class_name): bool {
    if ($this->node instanceof $class_name) {
      return true;
    } else if ($this->parent !== null) {
      return $this->parent->has_parent($class_name);
    } else {
      return false;
    }
  }
}
