<?php

namespace Cthulhu\ir\nodes;

class NullaryForm extends Form {
  public function children(): array {
    return [ $this->name ];
  }

  public function from_children(array $children): NullaryForm {
    return new NullaryForm(...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('form')
      ->space()
      ->then($this->name)
      ->paren_right();
  }
}
