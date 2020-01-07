<?php

namespace Cthulhu\ast\nodes;

class PathExpr extends Expr {
  public PathNode $path;

  public function __construct(PathNode $path) {
    assert($path->tail instanceof LowerNameNode);
    parent::__construct($path->span);
    $this->path = $path;
  }
}
