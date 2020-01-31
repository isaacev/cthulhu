<?php

namespace Cthulhu\ir\nodes;

class VariablePattern extends Pattern {
  public Name $name;

  public function __construct(Name $name) {
    parent::__construct($name->type);
    $this->name = $name;
  }

  public function children(): array {
    return [];
  }

  public function from_children(array $children): VariablePattern {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->name);
  }

  public function __toString(): string {
    return $this->name;
  }
}
