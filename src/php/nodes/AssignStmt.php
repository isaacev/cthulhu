<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;

class AssignStmt extends Stmt {
  public Node $assignee;
  public Expr $expr;

  public function __construct(Node $assignee, Expr $expr, ?Stmt $next) {
    parent::__construct($next);
    $this->assignee = $assignee;
    $this->expr     = $expr;
  }

  public function children(): array {
    return [
      $this->assignee,
      $this->expr,
    ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1], $this->next);
  }

  public function from_successor(?EditableSuccessor $successor): AssignStmt {
    assert($successor === null || $successor instanceof Stmt);
    return new AssignStmt($this->assignee, $this->expr, $successor);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline_then_indent()
      ->then($this->assignee)
      ->space()
      ->equals()
      ->space()
      ->expr($this->expr)
      ->semicolon()
      ->then($this->next);
  }
}
