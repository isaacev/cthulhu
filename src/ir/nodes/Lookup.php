<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class Lookup extends Expr {
  public Expr $root;
  public Name $field;

  public function __construct(Type $type, Expr $root, Name $field) {
    parent::__construct($type);
    $this->root  = $root;
    $this->field = $field;
  }

  public function children(): array {
    return [ $this->root, $this->field ];
  }

  public function from_children(array $children): Node {
    return new self($this->type, $children[0], $children[1]);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('lookup')
      ->space()
      ->then($this->root)
      ->space()
      ->then($this->field)
      ->paren_right();
  }
}
