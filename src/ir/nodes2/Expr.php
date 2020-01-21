<?php

namespace Cthulhu\ir\nodes2;

use Cthulhu\ir\types\hm\Type;

abstract class Expr extends Node {
  public Type $type;

  public function __construct(Type $type) {
    parent::__construct();
    $this->type = $type;
  }
}
