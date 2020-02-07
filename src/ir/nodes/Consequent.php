<?php

namespace Cthulhu\ir\nodes;

class Consequent extends Stmts {
  public function from_children(array $children): Consequent {
    return new Consequent(...$children);
  }
}
