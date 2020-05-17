<?php

namespace Cthulhu\ir\names;

class ClosedScope extends NestedScope {
  /* @var TermBinding[] $closed_bindings */
  public array $closed_bindings = [];

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public function get_public_or_private_term_binding(string $name): ?TermBinding {
    if ($this->terms->has_name($name)) {
      return $this->terms->get_name($name);
    } else if ($binding = $this->parent->get_public_or_private_term_binding($name)) {
      $this->closed_bindings[$binding->name] = $binding;
      return $binding;
    } else {
      return null;
    }
  }
}
