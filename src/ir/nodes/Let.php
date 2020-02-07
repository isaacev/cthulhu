<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableSuccessor;

class Let extends Stmt {
  public Name $name;
  public Expr $expr;

  public function __construct(Name $name, Expr $expr, ?Stmt $next) {
    parent::__construct($next);
    $this->name = $name;
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->name, $this->expr ];
  }

  public function from_children(array $children): Let {
    return (new Let($children[0], $children[1], $this->next))
      ->copy($this);
  }

  public function from_successor(?EditableSuccessor $successor): Let {
    assert($successor === null || $successor instanceof Stmt);
    return (new Let($this->name, $this->expr, $successor))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline()
      ->indent()
      ->paren_left()
      ->keyword('let')
      ->space()
      ->then($this->name)
      ->increase_indentation()
      ->newline()
      ->indent()
      ->then($this->expr)
      ->decrease_indentation()
      ->paren_right()
      ->then($this->next ?? (new Builder));
  }
}
