<?php

namespace Cthulhu\Codegen;

use Cthulhu\IR\BlockScope;

class PendingBlock {
  public $scope;
  public $stmts;

  function __construct(BlockScope $scope) {
    $this->scope = $scope;
    $this->stmts = [];
  }

  public function push_stmt(PHP\Stmt $stmt): void {
    array_push($this->stmts, $stmt);
  }
}
