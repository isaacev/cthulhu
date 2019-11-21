<?php

namespace Cthulhu\ir\patterns;

class StrPattern extends Pattern {
  public $value;

  function __construct(string $value) {
    $this->value = $value;
  }

  function __toString(): string {
    return '"' . $this->value . '"';
  }
}
