<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class ReturnStmt extends Stmt {
  public $expr;

  function __constructor(PHP\Expr $expr) {
    $this->expr = $expr;
  }

  public function write(Writer $writer): Writer {
    return $writer->keyword('return')
                  ->node($this->expr)
                  ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ReturnStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
