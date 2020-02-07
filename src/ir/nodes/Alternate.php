<?php

namespace Cthulhu\ir\nodes;

class Alternate extends Stmts {
  public function from_children(array $children): Alternate {
    return new Alternate(...$children);
  }
}
