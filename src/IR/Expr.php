<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

abstract class Expr extends Node {
  public abstract function type(): Type;
}
