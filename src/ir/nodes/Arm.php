<?php

namespace Cthulhu\ir\nodes;

class Arm extends Node {
  public Pattern $pattern;
  public Expr $handler;

  public function __construct(Pattern $pattern, Expr $handler) {
    parent::__construct();
    $this->pattern = $pattern;
    $this->handler = $handler;
  }

  public function children(): array {
    return [ $this->pattern, $this->handler ];
  }

  public function from_children(array $children): Arm {
    return new self(...$children);
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
