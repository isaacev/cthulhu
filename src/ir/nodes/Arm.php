<?php

namespace Cthulhu\ir\nodes;

class Arm extends Node {
  public Pattern $pattern;
  public Handler $handler;

  public function __construct(Pattern $pattern, Handler $handler) {
    parent::__construct();
    $this->pattern = $pattern;
    $this->handler = $handler;
  }

  public function children(): array {
    return [ $this->pattern, $this->handler ];
  }

  public function from_children(array $children): Arm {
    return new Arm(...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('case')
      ->space()
      ->then($this->pattern)
      ->space()
      ->then($this->handler)
      ->paren_right();
  }
}
