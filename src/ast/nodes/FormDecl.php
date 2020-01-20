<?php

namespace Cthulhu\ast\nodes;

abstract class FormDecl extends Node {
  public UpperName $name;

  public function __construct(UpperName $name) {
    parent::__construct();
    $this->name = $name;
  }
}
