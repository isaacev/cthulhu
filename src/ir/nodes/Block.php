<?php

namespace Cthulhu\ir\nodes;

class Block extends Expr {
  public $stmts;

  function __construct(array $stmts) {
    parent::__construct();
    $this->stmts = $stmts;
  }

  function last_stmt(): ?Stmt {
    return empty($this->stmts) ? null : end($this->stmts);
  }

  function children(): array {
    return $this->stmts;
  }
}
