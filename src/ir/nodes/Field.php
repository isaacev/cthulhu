<?php

namespace Cthulhu\ir\nodes;

class Field extends Node {
  public Name $name;
  public Expr $expr;

  public function __construct(Name $name, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->name, $this->expr ];
  }

  public function from_children(array $children): Field {
    return new Field(...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword($this->name)
      ->space()
      ->then($this->expr)
      ->paren_right();
  }
}
