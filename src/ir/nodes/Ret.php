<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableSuccessor;

class Ret extends Stmt {
  public Expr $expr;

  public function __construct(Expr $expr, ?Stmt $next) {
    parent::__construct($next);
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $children): Ret {
    return (new Ret($children[0], $this->next))
      ->copy($this);
  }

  public function from_successor(?EditableSuccessor $successor): Ret {
    assert($successor === null || $successor instanceof Stmt);
    return (new Ret($this->expr, $successor))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline()
      ->indent()
      ->paren_left()
      ->keyword('ret')
      ->space()
      ->then($this->expr)
      ->paren_right()
      ->then($this->next ?? (new Builder));
  }
}
