<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Errors\UndeclaredVariable;
use Cthulhu\Types\Type;

class BlockScope implements MutableScope {
  private $parent;
  private $latest_binding = null;

  function __construct(?Scope $parent = null) {
    $this->parent = $parent;
  }

  public function has_binding(string $name): bool {
    $lookup = $this->latest_binding
      ? $this->latest_binding->lookup($name)
      : null;

    return $lookup !== null;
  }

  public function get_binding(string $name): Binding {
    $lookup = $this->latest_binding
      ? $this->latest_binding->lookup($name)
      : null;

    if ($lookup === null) {
      if ($this->parent) {
        return $this->parent->get_binding($name);
      } else {
        throw new UndeclaredVariable($name);
      }
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
    if ($this->parent !== null) {
      return array_merge([ $this ], $this->parent->chain());
    } else {
      return [ $this ];
    }
  }
}
