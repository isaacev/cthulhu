<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class AssignStmt extends Stmt {
  public $name;
  public $expr;

  function __construct(string $name, Expr $expr) {
    $this->name = $name;
    $this->expr = $expr;
  }

  public function build(): Builder {
    return (new Builder)
      ->variable($this->name)
      ->equals()
      ->expr($this->expr)
      ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'AssignStmt',
      'name' => $this->name,
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
