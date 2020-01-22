<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class PropertyNode extends Node {
  public bool $is_public;
  public Variable $name;

  public function __construct(bool $is_public, Variable $name) {
    parent::__construct();
    $this->is_public = $is_public;
    $this->name      = $name;
  }

  public function children(): array {
    return [ $this->name ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->is_public, $nodes[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword($this->is_public ? 'public' : 'private')
      ->space()
      ->then($this->name)
      ->semicolon();
  }
}
