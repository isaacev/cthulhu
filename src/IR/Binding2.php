<?php

namespace Cthulhu\IR;

class Binding2 {
  public $parent;
  public $symbol;

  function __construct(?Binding2 $parent, Symbol $symbol, string $name) {
    $this->parent = $parent;
    $this->symbol = $symbol;
    $this->name = $name;
  }

  public function lookup(string $name): ?Symbol {
    if ($this->name === $name) {
      return $this->symbol;
    } else if ($this->parent) {
      return $this->parent->lookup($name);
    } else {
      return null;
    }
  }
}
