<?php

namespace Cthulhu\ir\nodes;

abstract class Item extends Node {
  use traits\Attributes;

  function __construct(array $attrs) {
    parent::__construct();
    $this->attrs = $attrs;
  }
}