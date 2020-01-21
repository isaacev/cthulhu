<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableNodelike;

class Root extends Node {
  public ?Module $module;

  public function __construct(?Module $module) {
    parent::__construct();
    $this->module = $module;
  }

  public function children(): array {
    return [ $this->module ];
  }

  public function from_children(array $children): EditableNodelike {
    return (new self(...$children))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('root')
      ->space()
      ->increase_indentation()
      ->then($this->module ?? (new Builder))
      ->decrease_indentation()
      ->paren_right();
  }
}
