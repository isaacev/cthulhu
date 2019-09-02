<?php

namespace Cthulhu\IR;

class ModuleScope {
  public static function from_array(string $name, array $names): self {
    $scope =  new ModuleScope(null, $name);
    foreach ($names as $name => $type) {
      $symbol = new Symbol($name, $scope->symbol);
      $scope->add($symbol, $type);
    }
    return $scope;
  }

  public $parent;
  public $symbol;
  private $table;

  function __construct(?self $parent, string $name) {
    $this->parent = $parent;
    $this->symbol = new Symbol($name, $parent ? $this->parent->symbol : null);
    $this->table = [];
  }

  public function chain(): array {
    return array_merge([ $this->symbol ], $this->ancestor_symbols());
  }

  private function ancestor_symbols(): array {
    $ancestor_symbols = [];
    $ancestor = $this->parent;
    while ($ancestor !== null) {
      $ancestor_symbols[] = $ancestor->symbol;
      $ancestor = $ancestor->parent;
    }
    return $ancestor_symbols;
  }

  public function add(Symbol $symbol, $type_or_module): void {
    $this->table[$symbol->id] = [$symbol, $type_or_module];
  }

  public function has(Symbol $symbol): bool {
    return array_key_exists($symbol->id, $this->table);
  }

  public function to_symbol(string $name): Symbol {
    foreach ($this->table as $id => list($symbol, $type)) {
      if ($symbol->name === $name) {
        return $symbol;
      }
    }
    throw new \Exception("no submodule $name within $this->symbol");
  }

  public function lookup(Symbol $symbol) {
    return $this->table[$symbol->id][1];
  }
}
