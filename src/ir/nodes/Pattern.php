<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

abstract class Pattern extends Node {
  public Type $type;

  public function __construct(Type $type) {
    parent::__construct();
    $this->type = $type;
  }

  abstract public function __toString(): string;
}
