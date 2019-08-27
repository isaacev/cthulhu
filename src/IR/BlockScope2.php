<?php

namespace Cthulhu\IR;

class BlockScope2 implements Scope2 {
  public $parent;
  public $names;

  function __construct(Scope2 $parent) {
    $this->parent = $parent;
    $this->names = [];
  }

  function add_name(string $name, Symbol2 $symbol) {
    $this->names[$name] = $symbol;
  }

  function has_name(string $name): bool {
    return array_key_exists($this->names, $name);
  }

  function get_name(string $name): Symbol2 {
    return $this->names[$name];
  }
}
