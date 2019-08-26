<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class FuncScope implements MutableScope {
  public $parent;
  public $latest_binding = null;

  function __construct(Scope $parent) {
    $this->parent = $parent;
  }

  public function has_binding(string $name): bool {
    $lookup = $this->latest_binding
      ? $this->latest_binding->lookup($name)
      : null;

    return $lookup || $this->parent->has_binding($name);
  }

  public function get_binding(string $name): Binding {
    $lookup = $this->latest_binding
      ? $this->latest_binding->lookup($name)
      : null;

    if ($lookup === null) {
      return $this->parent->get_binding($name);
    }

    return $lookup;
  }

  public function new_binding(string $name, Type $type): IdentifierNode {
    $ident = new IdentifierNode($name, new Symbol($this));
    $binding = new Binding($this->latest_binding, $ident, $type);
    $this->latest_binding = $binding;
    return $ident;
  }

  public function chain(): array {
    if ($this->parent) {
      return array_merge([ $this ], $this->parent->chain());
    } else {
      return [ $this ];
    }
  }
}
