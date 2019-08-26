<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR\Symbol;

abstract class Scope2 {
  protected $symbol_table = [];

  protected function add_symbol_to_table(Symbol $symbol, string $name) {
    $this->symbol_table[$symbol->id] = $name;
  }

  protected function has_symbol_in_table(Symbol $symbol): bool {
    return array_key_exists($symbol->id, $this->symbol_table);
  }

  protected function get_name_from_table(Symbol $symbol): string {
    return $this->symbol_table[$symbol->id];
  }
}
