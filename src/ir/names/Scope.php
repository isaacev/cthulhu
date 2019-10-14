<?php

namespace Cthulhu\ir\names;

class Scope {
  public $table = [];

  public function has_name(string $name): bool {
    return array_key_exists($name, $this->table);
  }

  public function add_binding(string $name, Symbol $symbol): void {
    $this->table[$name] = $symbol;
  }

  public function get_name(string $name): ?Symbol {
    return $this->table[$name];
  }
}
