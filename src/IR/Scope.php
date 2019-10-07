<?php

namespace Cthulhu\IR;

abstract class Scope {
  protected $table = [];

  function add_binding(Binding $binding): void {
    $this->table[$binding->symbol->id] = $binding;
  }

  function has_binding(Symbol $symbol): bool {
    return array_key_exists($symbol->id, $this->table);
  }

  function resolve_name(string $name): ?Binding {
    foreach ($this->table as $id => $binding) {
      if ($binding->matches_name($name)) {
        return $binding;
      }
    }
    return null;
  }
}
