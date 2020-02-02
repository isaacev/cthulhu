<?php

namespace Cthulhu\ir\nodes;

class NamedFormField extends Node {
  public Name $name;
  public Pattern $pattern;

  public function __construct(Name $name, Pattern $pattern) {
    parent::__construct();
    $this->name    = $name;
    $this->pattern = $pattern;
  }

  public function children(): array {
    return [ $this->name, $this->pattern ];
  }

  public function from_children(array $children): NamedFormField {
    return new NamedFormField(...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->name)
      ->colon()
      ->space()
      ->then($this->pattern);
  }

  public function __toString(): string {
    return "$this->name: $this->pattern";
  }
}
