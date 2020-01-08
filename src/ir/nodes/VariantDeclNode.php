<?php

namespace Cthulhu\ir\nodes;

abstract class VariantDeclNode extends Node {
  public Name $name;

  public function __construct(Name $name) {
    parent::__construct();
    $this->name = $name;
  }
}
