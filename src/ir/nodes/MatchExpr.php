<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class MatchExpr extends BranchExpr {
  public Disc $disc;
  public Arms $arms;

  public function __construct(Type $type, Disc $disc, Arms $arms) {
    parent::__construct($type);
    $this->disc = $disc;
    $this->arms = $arms;
  }

  public function children(): array {
    return [ $this->disc, $this->arms ];
  }

  public function from_children(array $children): MatchExpr {
    return new MatchExpr($this->type, ...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('match')
      ->space()
      ->then($this->disc)
      ->newline()
      ->increase_indentation()
      ->then($this->arms)
      ->decrease_indentation()
      ->paren_right();
  }
}
