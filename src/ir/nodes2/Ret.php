<?php

namespace Cthulhu\ir\nodes2;

use Cthulhu\lib\trees\EditableNodelike;

class Ret extends Stmt {
  public Expr $expr;

  public function __construct(Expr $expr, ?Stmt $next) {
    parent::__construct($next);
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->expr, $this->next ];
  }

  public function from_children(array $children): EditableNodelike {
    return (new self(...$children))
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
