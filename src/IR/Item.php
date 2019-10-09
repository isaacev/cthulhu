<?php

namespace Cthulhu\IR;

abstract class Item extends Node {
  public $attrs;

  function __construct(array $attrs) {
    $this->attrs = $attrs;
  }

  function get_attr(string $attr, $fallback = null) {
    if (array_key_exists($attr, $this->attrs)) {
      return $this->attrs;
    } else {
      return $fallback;
    }
  }
}
