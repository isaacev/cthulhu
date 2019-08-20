<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Errors\UndeclaredVariable;
use Cthulhu\Types\Type;

class GlobalScope implements Scope {
  public $latest_binding = null;

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
      throw new UndeclaredVariable($name);
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
