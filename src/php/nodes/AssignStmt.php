<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class AssignStmt extends Stmt {
  public $assignee;
  public $expr;

  function __construct(Node $assignee, Expr $expr) {
    parent::__construct();
    $this->assignee = $assignee;
    $this->expr = $expr;
  }

  public function to_children(): array {
    return [
      $this->assignee,
      $this->expr,
    ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->assignee)
      ->space()
      ->equals()
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }
}
