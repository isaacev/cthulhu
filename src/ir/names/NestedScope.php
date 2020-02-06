<?php

namespace Cthulhu\ir\names;

class NestedScope extends Scope {
  public Scope $parent;

  public function __construct(Scope $parent) {
    $this->parent = $parent;
  }

  public function get_name(string $name): ?Binding {
    if ($this->has_name($name)) {
      return $this->table[$name];
    } else {
      return $this->parent->get_name($name);
    }
  }
}
