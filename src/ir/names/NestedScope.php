<?php

namespace Cthulhu\ir\names;

class NestedScope extends Scope {
  public Scope $parent;

  public function __construct(Scope $parent) {
    parent::__construct();
    $this->parent = $parent;
  }

  public function get_public_or_private_term_binding(string $name): ?TermBinding {
    return parent::get_public_or_private_term_binding($name)
      ?? $this->parent->get_public_or_private_term_binding($name);
  }
}
