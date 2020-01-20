<?php

namespace Cthulhu\ast\nodes;

class BlockNode extends Expr {
  public array $stmts;

  public function __construct(array $stmts) {
    parent::__construct();
    $this->stmts = $stmts;
  }

  public function children(): array {
    return $this->stmts;
  }
}
