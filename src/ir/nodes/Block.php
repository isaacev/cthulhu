<?php

namespace Cthulhu\ir\nodes;

class Block extends Expr {
  public array $stmts;

  /**
   * @param Stmt[] $stmts
   */
  function __construct(array $stmts) {
    parent::__construct();
    assert(count($stmts) > 0);
    assert(end($stmts) instanceof ReturnStmt);
    $this->stmts = $stmts;
  }

  function children(): array {
    return $this->stmts;
  }
}
