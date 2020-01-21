<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableNodelike;

class Let extends Stmt {
  public ?Name $name;
  public Expr $expr;

  public function __construct(?Name $name, Expr $expr, ?Stmt $next) {
    parent::__construct($next);
    $this->name = $name;
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->name, $this->expr, $this->next ];
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
      ->keyword('let')
      ->space()
      ->then($this->name ? $this->name : (new Builder)->keyword('_'))
      ->increase_indentation()
      ->newline()
      ->indent()
      ->then($this->expr)
      ->decrease_indentation()
      ->paren_right()
      ->then($this->next ?? (new Builder));
  }
}
