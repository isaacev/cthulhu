<?php

namespace Cthulhu\ast\nodes;

class VariablePattern extends Pattern {
  public LowerNameNode $name;

  public function __construct(LowerNameNode $name) {
    parent::__construct($name->span);
    $this->name = $name;
  }
}
