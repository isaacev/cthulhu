<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class ExprStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function write(Writer $writer): Writer {
    return $writer->node($this->expr)
                  ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ExprStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
