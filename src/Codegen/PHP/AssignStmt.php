<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class AssignStmt extends Stmt {
  public $name;
  public $expr;

  function __construct(string $name, Expr $expr) {
    $this->name = $name;
    $this->expr = $expr;
  }

  public function write(Writer $writer): Writer {
    return $writer->variable($this->name)
                  ->equals()
                  ->node($this->expr)
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
