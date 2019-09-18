<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class BlockScope {
  public $parent;
  private $table;

  function __construct($parent) {
    $this->parent = $parent;
    $this->table = [];
  }

  public function add(Symbol $symbol, Type $type): void {
    $this->table[$symbol->id] = [$symbol, $type];
  }

  public function has(Symbol $symbol): bool {
    return array_key_exists($symbol->id, $this->table);
  }

  public function to_symbol(string $name): ?Symbol {
    foreach ($this->table as $id => list($symbol, $type)) {
      if ($symbol->name === $name) {
        return $symbol;
      }
    }
    return $this->parent->to_symbol($name);
  }

  public function lookup(Symbol $symbol): Type {
    if ($this->has($symbol)) {
      return $this->table[$symbol->id][1];
    } else {
      return $this->parent->lookup($symbol);
    }
  }
}
