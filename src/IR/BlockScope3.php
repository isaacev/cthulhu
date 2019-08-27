<?php

namespace Cthulhu\IR;

class BlockScope3 {
  public $parent;
  private $table;

  function __construct($parent) {
    $this->parent = $parent;
    $this->table = [];
  }

  public function add(Symbol3 $symbol, Type $type): void {
    $this->table[$symbol->id] = [$symbol, $type];
  }

  public function has(Symbol3 $symbol): bool {
    return array_key_exists($this->table, $symbol->id);
  }

  public function lookup(Symbol3 $symbol): Type {
    if ($this->has($symbol)) {
      return $this->table[$symbol->id][1];
    } else {
      return $this->parent->lookup($symbol);
    }
  }
}
