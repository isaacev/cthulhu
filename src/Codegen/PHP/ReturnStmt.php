<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class ReturnStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('ReturnStmt', $table)) {
      $table['ReturnStmt']($this);
    }

    $this->expr->visit($table);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('return')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ReturnStmt'
    ];
  }
}
