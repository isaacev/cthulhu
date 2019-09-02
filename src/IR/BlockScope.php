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
    return array_key_exists($this->table, $symbol->id);
  }

  public function lookup(Symbol $symbol): Type {
    if ($this->has($symbol)) {
      return $this->table[$symbol->id][1];
    } else {
      return $this->parent->lookup($symbol);
    }
  }
}
