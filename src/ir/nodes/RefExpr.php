<?php

namespace Cthulhu\ir\nodes;

class RefExpr extends Expr {
  public $ref;

  function __construct(Ref $ref) {
    parent::__construct();
    $this->ref = $ref;
  }

  function children(): array {
    return [ $this->ref ];
  }
}
