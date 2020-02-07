<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class Apply extends Expr {
  public Expr $callee;
  public Exprs $args;

  public function __construct(Type $type, Expr $callee, Exprs $args) {
    parent::__construct($type);
    $this->callee = $callee;
    $this->args   = $args;
  }

  public function children(): array {
    return [ $this->callee, $this->args ];
  }

  public function from_children(array $children): Apply {
    return (new Apply($this->type, $children[0], $children[1]))
      ->copy($this);
  }

  public function build(): Builder {
    if (count($this->args) === 0) {
      $args = (new Builder)
        ->paren_left()
        ->paren_right();
    } else {
      $args = (new Builder)
        ->paren_left()
        ->increase_indentation()
        ->then($this->args)
        ->decrease_indentation()
        ->paren_right();
    }

    return (new Builder)
      ->paren_left()
      ->keyword('apply')
      ->space()
      ->then($this->callee)
      ->space()
      ->then($args)
      ->paren_right();
  }
}
