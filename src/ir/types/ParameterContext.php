<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names\Symbol;

class ParameterContext {
  private ?self $parent;
  private array $table = [];

  public function __construct(?self $parent) {
    $this->parent = $parent;
  }

  public function write(Symbol $symbol, Type $type): void {
    $this->table[$symbol->get_id()] = $type;
  }

  public function read(Symbol $symbol): ?Type {
    if (array_key_exists($symbol->get_id(), $this->table)) {
      return $this->table[$symbol->get_id()];
    } else if ($this->parent) {
      return $this->parent->read($symbol);
    } else {
      return null;
    }
  }
}
