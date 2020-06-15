<?php

namespace Cthulhu\ast\nodes;

class UnreachableExpr extends Expr {
  public int $line;
  public string $file;

  public function __construct(int $line, string $file) {
    parent::__construct();
    $this->line = $line;
    $this->file = $file;
  }

  public function children(): array {
    return [];
  }
}
