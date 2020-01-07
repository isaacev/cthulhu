<?php

namespace Cthulhu\ast\nodes;

class ConstPattern extends Pattern {
  public Literal $literal;

  public function __construct(Literal $literal) {
    parent::__construct($literal->span);
    $this->literal = $literal;
  }
}
