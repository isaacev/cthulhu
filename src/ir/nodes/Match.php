<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Match extends Expr {
  public Expr $discriminant;
  public Arms $arms;

  public function __construct(Type $type, Expr $discriminant, Arms $arms) {
    parent::__construct($type);
    $this->discriminant = $discriminant;
    $this->arms         = $arms;
  }

  public function children(): array {
    return [ $this->discriminant, $this->arms ];
  }

  public function from_children(array $children): EditableNodelike {
    return new Match($this->type, ...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('match')
      ->space()
      ->then($this->discriminant)
      ->newline()
      ->increase_indentation()
      ->then($this->arms)
      ->decrease_indentation()
      ->paren_right();
  }
}
