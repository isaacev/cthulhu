<?php

namespace Cthulhu\IR;

class ClosureScope3 {
  public $parent;
  private $closed;
  private $table;

  function __construct(BlockScope3 $parent, array $params) {
    $this->parent = $parent;
    $this->closed = [];
    $this->table = [];

    foreach ($params as list($symbol, $type)) {
      $this->add($symbol, $type);
    }
  }

  private function add(Symbol3 $symbol, Type $type): void {
    $this->table[$symbol->id] = [$symbol, $type];
  }

  public function has(Symbol3 $symbol): bool {
    return array_key_exists($this->table, $symbol->id);
  }

  public function lookup(Symbol3 $symbol): Type {
    if ($this->has($symbol)) {
      return $this->table[$symbol->id];
    } else {
      $this->closed[] = $symbol;
      return $this->parent->lookup($symbol);
    }
  }
}
