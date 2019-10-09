<?php

namespace Cthulhu\IR;

class ModuleScope extends Scope {
  public $parent;
  public $symbol;

  function __construct(?ModuleScope $parent, string $name) {
    $this->parent = $parent;
    $this->symbol = new Symbol($name, null, $parent ? $parent->symbol : null);
  }
}
