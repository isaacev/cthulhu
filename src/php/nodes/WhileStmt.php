<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;

class WhileStmt extends Stmt {
  public Expr $condition;
  public BlockNode $consequent;

  public function __construct(Expr $condition, BlockNode $consequent, ?Stmt $next) {
    parent::__construct($next);
    $this->condition  = $condition;
    $this->consequent = $consequent;
  }

  public function children(): array {
    return [ $this->condition, $this->consequent ];
  }

  public function from_children(array $children): WhileStmt {
    return new WhileStmt($children[0], $children[1], $this->next);
  }

  public function from_successor(?EditableSuccessor $successor): WhileStmt {
    assert($successor === null || $successor instanceof Stmt);
    return new WhileStmt($this->condition, $this->consequent, $successor);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline_then_indent()
      ->keyword('while')
      ->space()
      ->paren_left()
      ->then($this->condition)
      ->paren_right()
      ->space()
      ->then($this->consequent)
      ->then($this->next ?? (new Builder));
  }
}
