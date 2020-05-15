<?php

namespace Cthulhu\ir\names;

class Scope {
  /* @var Binding[] $table */
  protected array $table = [];

  public function has_name(string $name): bool {
    return array_key_exists($name, $this->table);
  }

  public function add_binding(Binding $binding): void {
    $this->table[$binding->name] = $binding;
  }

  public function get_name(string $name): ?Binding {
    if ($this->has_name($name)) {
      return $this->table[$name];
    } else {
      return null;
    }
  }

  public function get_public_name(string $name): ?Binding {
    if (($binding = $this->get_name($name)) && $binding->is_public) {
      return $binding;
    } else {
      return null;
    }
  }

  /**
   * @return Binding[]
   */
  public function get_any_bindings(): array {
    return $this->table;
  }

  /**
   * @return Binding[]
   */
  public function get_public_bindings(): array {
    $bindings = [];
    foreach ($this->table as $name => $binding) {
      if ($binding->is_public) {
        $bindings[$name] = $binding;
      }
    }
    return $bindings;
  }

  /**
   * @return string[]
   */
  public function get_any_names(): array {
    return array_keys($this->table);
  }

  /**
   * @return string[]
   */
  public function get_public_names(): array {
    return array_keys($this->get_public_bindings());
  }
}
