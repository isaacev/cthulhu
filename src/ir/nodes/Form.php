<?php

namespace Cthulhu\ir\nodes;

abstract class Form extends Node {
  public Name $name;

  public function __construct(Name $name) {
    parent::__construct();
    $this->name = $name;
  }
}
