<?php

namespace Cthulhu\Codegen\PHP;

abstract class Expr extends Node {
  public function precedence(): int {
    return PHP_INT_MAX;
  }
}
