<?php

namespace Cthulhu\ir\names;

class ClosedScope extends NestedScope {
  /* @var Binding[] $closed_bindings */
  public array $closed_bindings = [];

  public function get_name(string $name): ?Binding {
    if ($this->has_name($name)) {
      return $this->table[$name];
    } else if ($binding = $this->parent->get_name($name)) {
      $this->closed_bindings[$binding->name] = $binding;
      return $binding;
    } else {
      return null;
    }
  }
}
