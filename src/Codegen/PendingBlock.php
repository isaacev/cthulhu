<?php

namespace Cthulhu\Codegen;

class PendingBlock {
  public $stmts;

  function __construct() {
    $this->stmts = [];
  }

  public function push_stmt(PHP\Stmt $stmt): void {
    array_push($this->stmts, $stmt);
  }
}
