<?php

namespace Cthulhu\ast;

class ConstPattern extends Pattern {
  public $literal;

  function __construct(Literal $literal) {
    parent::__construct($literal->span);
    $this->literal = $literal;
  }
}
