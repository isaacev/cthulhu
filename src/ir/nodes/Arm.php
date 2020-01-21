<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ast\nodes\Pattern;
use Cthulhu\lib\trees\EditableNodelike;

class Arm extends Node {
  public Pattern $pattern;
  public Expr $handler;

  public function __construct(Pattern $pattern, Expr $handler) {
    parent::__construct();
    $this->pattern = $pattern;
    $this->handler = $handler;
  }

  public function children(): array {
    return [ $this->handler ];
  }

  public function from_children(array $children): EditableNodelike {
    return new self($this->pattern, $children[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('case')
      ->space()
      ->pattern("$this->pattern")
      ->space()
      ->then($this->handler)
      ->paren_right();
  }
}
