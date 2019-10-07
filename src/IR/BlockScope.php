<?php

namespace Cthulhu\IR;

class BlockScope extends Scope {
  public $parent;

  function __construct(Scope $parent) {
    $this->parent = $parent;
  }

  function resolve_name(string $name): ?Binding {
    if ($binding = parent::resolve_name($name)) {
      return $binding;
    } else if ($this->parent) {
      return $this->parent->resolve_name($name);
    } else {
      return null;
    }
  }
}
