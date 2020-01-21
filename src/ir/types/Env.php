<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names\Symbol;

class Env {
  /* @var hm\Type[] $table */
  public array $table = [];

  public function has(Symbol $symbol): bool {
    return array_key_exists($symbol->get_id(), $this->table);
  }

  public function read(Symbol $symbol): hm\Type {
    if ($this->has($symbol)) {
      return $this->table[$symbol->get_id()];
    }
    echo "unknown symbol named: " . $symbol->get('text') . PHP_EOL;
    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  public function write(Symbol $symbol, hm\Type $value): void {
    $this->table[$symbol->get_id()] = $value;
  }
}
