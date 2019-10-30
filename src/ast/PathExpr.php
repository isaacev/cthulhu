<?php

namespace Cthulhu\ast;

class PathExpr extends Expr {
  public $path;

  function __construct(PathNode $path) {
    parent::__construct($path->span);
    $this->path = $path;
  }
}
