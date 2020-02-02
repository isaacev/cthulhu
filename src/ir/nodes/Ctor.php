<?php

namespace Cthulhu\ir\nodes;

class Ctor extends Expr {
  public Name $name;
  public Expr $args;

  public function __construct(Name $name, Expr $args) {
    parent::__construct($name->type);
    $this->name = $name;
    $this->args = $args;
  }

  public function children(): array {
    return [ $this->name, $this->args ];
  }

  public function from_children(array $children): Ctor {
    return new self(...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('ctor')
      ->then($this->name)
      ->space()
      ->then($this->args)
      ->paren_right();
  }
}
