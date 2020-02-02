<?php

namespace Cthulhu\ir\types;

class ParameterContext {
  private ?self $parent;
  private array $table = [];

  public function __construct(?self $parent) {
    $this->parent = $parent;
  }

  public function write(int $type_id, Type $type): void {
    $this->table[$type_id] = $type;
  }

  public function read(int $type_id): ?Type {
    if (array_key_exists($type_id, $this->table)) {
      return $this->table[$type_id];
    } else if ($this->parent) {
      return $this->parent->read($type_id);
    } else {
      return null;
    }
  }
}
