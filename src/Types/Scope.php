<?php

namespace Cthulhu\Types;

class Scope {
  private $table;
  private $parent;

  function __construct(?Scope $parent) {
    $this->table = [];
    $this->parent = $parent;
  }

  public function set_local_variable(string $name, Type $type): void {
    $this->table[$name] = $type;
  }

  public function has_local_variable(string $name): bool {
    return array_key_exists($name, $this->table);
  }

  public function get_local_variable(string $name): Type {
    return $this->table[$name];
  }
}
