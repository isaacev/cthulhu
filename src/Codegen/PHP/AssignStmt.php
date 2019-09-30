<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class AssignStmt extends Stmt {
  public $assignee;
  public $expr;

  function __construct(Variable $assignee, Expr $expr) {
    $this->assignee = $assignee;
    $this->expr = $expr;
  }

  public function to_children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->assignee, $nodes[0]);
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

  public function jsonSerialize() {
    return [
      'type' => 'AssignStmt',
      'assignee' => $this->assignee->jsonSerialize(),
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
