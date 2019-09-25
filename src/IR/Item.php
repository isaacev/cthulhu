<?php

namespace Cthulhu\IR;

abstract class Item extends Node {
  public $attrs;

  function __construct(array $attrs) {
    $this->attrs = $attrs;
  }
}
