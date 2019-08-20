<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class Binding {
  public $parent;
  public $name;
  public $type;

  function __construct(?Binding $parent, string $name, Type $type) {
    $this->parent = $parent;
    $this->name = $name;
    $this->type = $type;
  }

  public function resolve(string $name): ?Type {
    if ($this->name === $name) {
      return $this->type;
    } else if ($this->parent) {
      return $this->parent->resolve($name);
    } else {
      return null;
    }
  }

  public function to_table(): array {
    $table = [ $this->name => $this->type ];
    if ($this->parent) {
      return array_merge($this->parent->to_table(), $table);
    } else {
      return $table;
    }
  }
}
