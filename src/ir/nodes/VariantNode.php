<?php

namespace Cthulhu\ir\nodes;

abstract class VariantNode extends Node {
  public $name;

  function __construct(Name $name) {
    parent::__construct();
    $this->name = $name;
  }
}
