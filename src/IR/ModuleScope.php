<?php

namespace Cthulhu\IR;

class ModuleScope implements Scope {
  public $name;
  public $submodules;
  public $block_scope;

  function __construct(string $name, BlockScope $block_scope) {
    $this->name = $name;
    $this->submodules = [];
    $this->block_scope = $block_scope;
  }

  public function get_submodule(string $name): ModuleScope {
    return $this->submodules[$name];
  }

  public function add_submodule(string $name, ModuleScope $submodule): void {
    $this->submodules[$name] = $submodule;
  }

  public function has_binding(string $name): bool {
    return $this->block_scope->has_binding($name);
  }

  public function get_binding(string $name): Symbol {
    return $this->block_scope->get_binding($name);
  }
}
