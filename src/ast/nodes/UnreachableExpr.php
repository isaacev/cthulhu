<?php

namespace Cthulhu\ast\nodes;

class UnreachableExpr extends Expr {
  public function children(): array {
    return [];
  }
}
