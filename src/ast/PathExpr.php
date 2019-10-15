<?php

namespace Cthulhu\ast;

class PathExpr extends Expr {
  public $path;

  function __construct(PathNode $path) {
    parent::__construct($path->span);
    $this->path = $path;
  }

  public function length(): int {
    return count($this->path->segments);
  }

  public function nth(int $n): IdentNode {
    return $this->path->segments[$n];
  }
}
