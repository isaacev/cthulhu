<?php

namespace Cthulhu\ir\nodes;

class RefExpr extends Expr {
  public Ref $ref;

  public function __construct(Ref $ref) {
    parent::__construct();
    $this->ref = $ref;
  }

  public function children(): array {
    return [ $this->ref ];
  }
}
