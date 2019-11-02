<?php

namespace Cthulhu\php\nodes;

abstract class Expr extends Node {
  public function precedence(): int {
    return Precedence::HIGHEST;
  }
}
