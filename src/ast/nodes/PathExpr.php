<?php

namespace Cthulhu\ast\nodes;

class PathExpr extends Expr {
  public PathNode $path;

  public function __construct(PathNode $path) {
    assert($path->tail instanceof LowerName);
    parent::__construct();
    $this->path = $path;
  }

  public function children(): array {
    return [ $this->path ];
  }
}
