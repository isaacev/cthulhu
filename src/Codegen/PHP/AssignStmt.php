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

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('AssignStmt', $table)) {
      $table['AssignStmt']($this);
    }

    $this->assignee->visit($table);
    $this->expr->visit($table);
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
