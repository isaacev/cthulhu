<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR\Symbol;

abstract class Scope {
  protected $symbol_table = [];
  protected $used_names = [];

  public function add_symbol_to_table(Symbol $symbol, string $name) {
    $this->symbol_table[$symbol->id] = $name;
    $this->used_names[] = $name;
  }

  public function has_symbol_in_table(Symbol $symbol): bool {
    return array_key_exists($symbol->id, $this->symbol_table);
  }

  public function get_name_from_table(Symbol $symbol): string {
    return $this->symbol_table[$symbol->id];
  }

  public function has_name_in_table(string $name): bool {
    return in_array($name, $this->used_names);
  }
}
