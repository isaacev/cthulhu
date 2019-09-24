<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class EchoStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('EchoStmt', $table)) {
      $table['EchoStmt']($this);
    }

    $this->expr->visit($table);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('echo')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }
}
