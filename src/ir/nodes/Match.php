<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Match extends Expr {
  public Expr $discriminant;
  public array $arms;

  /**
   * @param Type  $type
   * @param Expr  $discriminant
   * @param Arm[] $arms
   */
  public function __construct(Type $type, Expr $discriminant, array $arms) {
    parent::__construct($type);
    $this->discriminant = $discriminant;
    $this->arms         = $arms;
  }

  public function children(): array {
    return array_merge([ $this->discriminant ], $this->arms);
  }

  public function from_children(array $children): EditableNodelike {
    return new self($this->type, $children[0], array_slice($children, 1));
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('match')
      ->space()
      ->then($this->discriminant)
      ->newline()
      ->increase_indentation()
      ->indent()
      ->each($this->arms, (new Builder)
        ->newline()
        ->indent())
      ->decrease_indentation()
      ->paren_right();
  }
}
