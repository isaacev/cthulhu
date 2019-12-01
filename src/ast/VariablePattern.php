<?php

namespace Cthulhu\ast;

class VariablePattern extends Pattern {
  public LowerNameNode $name;

  function __construct(LowerNameNode $name) {
    parent::__construct($name->span);
    $this->name = $name;
  }
}
