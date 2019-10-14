<?php

namespace Cthulhu\ir\nodes\traits;

trait Attributes {
  public $attrs = [];

  public function get_attr(string $attr, $fallback = null) {
    return array_key_exists($attr, $this->attrs)
      ? $this->attrs
      : $fallback;
  }
}
