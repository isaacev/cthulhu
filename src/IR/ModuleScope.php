<?php

namespace Cthulhu\IR;

class ModuleScope implements Scope, \JsonSerializable {
  public $parent;
  public $symbol;
  public $identifier;
  public $submodules;
  public $block_scope;

  function __construct(?ModuleScope $parent, ?string $name, BlockScope $block_scope) {
    $this->parent = $parent;
    $this->symbol = new Symbol($parent);
    $this->identifier = $name ? new IdentifierNode($name, $this->symbol) : null;
    $this->submodules = [];
    $this->block_scope = $block_scope;
  }

  public function get_path_segments(): array {
    if ($this->identifier === null) {
      if ($this->parent === null) {
        return [];
      } else {
        return $this->parent->get_path_segments();
      }
    }

    $segments = [$this->identifier];
    if ($this->parent === null) {
      return $segments;
    } else {
      return array_merge($this->parent->get_path_segments(), $segments);
    }
  }

  public function get_submodule(string $name): ModuleScope {
    return $this->submodules[$name];
  }

  public function add_submodule(ModuleScope $submodule): void {
    $this->submodules[$submodule->identifier->name] = $submodule;
  }

  public function has_binding(string $name): bool {
    return $this->block_scope->has_binding($name);
  }

  public function get_binding(string $name): Binding {
    return $this->block_scope->get_binding($name);
  }

  public function chain(): array {
    if ($this->parent) {
      return array_merge([ $this ], $this->parent->chain());
    } else {
      return [ $this ];
    }
  }

  public function jsonSerialize() {
    return [
      'symbol' => $this->symbol->jsonSerialize()
    ];
  }
}
