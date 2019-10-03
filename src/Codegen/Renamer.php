<?php

namespace Cthulhu\Codegen;

use Cthulhu\IR;

class Renamer {
  protected $scope_stack = [];
  protected $rename_cache = [];

  public function push_scope(PHP\Scope $scope): void {
    $this->scope_stack[] = $scope;
  }

  public function current_scope(): PHP\Scope {
    return end($this->scope_stack);
  }

  public function pop_scope(): PHP\Scope {
    return array_pop($this->scope_stack);
  }

  public function get_reference(IR\Symbol $symbol): PHP\Reference {
    $segments = [];
    $sym = $symbol;
    while ($sym !== null) {
      $segments[] = $this->resolve($sym);
      $sym = $sym->parent;
    }
    return new PHP\Reference($symbol, array_reverse($segments));
  }

  public function get_php_global(string $builtin_name): PHP\Reference {
    $symbol = new IR\Symbol($builtin_name, null, null);
    return $this->get_reference($symbol);
  }

  public function get_variable(IR\Symbol $symbol): PHP\Variable {
    return new PHP\Variable($symbol, $this->resolve($symbol));
  }

  public function allocate_variable(string $initial_name): PHP\Variable {
    $symbol = new IR\Symbol($initial_name, null, null);
    return $this->get_variable($symbol);
  }

  public function resolve(IR\Symbol $symbol): string {
    if (array_key_exists($symbol->id, $this->rename_cache)) {
      return $this->rename_cache[$symbol->id];
    } else if ($this->current_scope()->has_symbol_in_table($symbol)) {
      $unique_name = $this->current_scope()->get_name_from_table($symbol);
      $this->rename_cache[$symbol->id] = $unique_name;
      return $unique_name;
    }

    $unique_name = $symbol->name;
    if ($this->not_valid($unique_name)) {
      $counter = 1;
      $unique_name = sprintf('%s_%d', $symbol->name, $counter);
      while ($this->not_valid($unique_name)) {
        $counter++;
      }
    }

    $this->rename_cache[$symbol->id] = $unique_name;
    $this->current_scope()->add_symbol_to_table($symbol, $unique_name);
    return $unique_name;
  }

  public function not_valid(string $name): bool {
    return self::is_reserved($name) || $this->current_scope()->has_name_in_table($name);
  }

  public static function is_reserved(string $name): bool {
    return in_array(strtolower($name), Reserved::WORDS);
  }
}
