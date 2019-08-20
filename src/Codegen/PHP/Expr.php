<?php

namespace Cthulhu\Codegen\PHP;

abstract class Expr extends Node {
  public abstract function precedence(): int;
}
