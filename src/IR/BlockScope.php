<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Errors\UndeclaredVariable;
use Cthulhu\Types\Type;

class BlockScope implements Scope {
  private $parent;
  private $latest_binding = null;

  function __construct(Scope $parent) {
    $this->parent = $parent;
  }

  public function has_binding(string $name): bool {
    $lookup = $this->latest_binding
      ? $this->latest_binding->lookup($name)
      : null;

    return $lookup !== null;
  }

  public function get_binding(string $name): Symbol {
    $lookup = $this->latest_binding
      ? $this->latest_binding->lookup($name)
      : null;

    if ($lookup === null) {
      return $this->parent->get_binding($name);
    }

    return $lookup;
  }

  public function new_binding(string $name, Type $type): Symbol {
    $symbol = new Symbol($type, $this);
    $binding = new Binding($this->latest_binding, $symbol, $name);
    $this->latest_binding = $binding;
    return $symbol;
  }
}
