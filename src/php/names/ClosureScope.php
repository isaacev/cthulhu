<?php


namespace Cthulhu\php\names;


class ClosureScope extends Scope {
  protected Scope $parent;

  public function __construct(Scope $parent) {
    parent::__construct();
    $this->parent = $parent;
  }

  public function has_name(string $name): bool {
    return parent::has_name($name) || $this->parent->has_name($name);
  }
}
