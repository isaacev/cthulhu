<?php

namespace Cthulhu\IR;

abstract class Expr extends Node {
  public abstract function return_type(): Types\Type;
}
