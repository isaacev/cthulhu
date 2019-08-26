<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class Binding {
  public $parent;
  public $identifier;
  public $type;

  function __construct(?Binding $parent, IdentifierNode $ident, Type $type) {
    $this->parent = $parent;
    $this->identifier = $ident;
    $this->type = $type;
  }

  public function lookup(string $name): ?Binding {
    if ($this->identifier->name === $name) {
      return $this;
    } else if ($this->parent) {
      return $this->parent->lookup($name);
    } else {
      return null;
    }
  }
}
