<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class SemiStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('SemiStmt', $table)) {
      $table['SemiStmt']($this);
    }

    $this->expr->visit($table);
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->expr)
      ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'SemiStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
