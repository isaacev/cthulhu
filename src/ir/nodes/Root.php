<?php

namespace Cthulhu\ir\nodes;

class Root extends Node {
  public ?Module $module;

  public function __construct(?Module $module) {
    parent::__construct();
    $this->module = $module;
  }

  public function children(): array {
    return [ $this->module ];
  }

  public function from_children(array $children): Root {
    return (new Root(...$children))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('root')
      ->space()
      ->increase_indentation()
      ->then($this->module)
      ->decrease_indentation()
      ->paren_right();
  }
}
