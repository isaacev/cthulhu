<?php

namespace Cthulhu\php\nodes;

abstract class Expr extends Node {
  public function precedence(): int {
    return PHP_INT_MAX;
  }
}
