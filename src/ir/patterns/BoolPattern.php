<?php

namespace Cthulhu\ir\patterns;

class BoolPattern extends Pattern {
  public bool $value;

  function __construct(bool $value) {
    $this->value = $value;
  }

  function __toString(): string {
    return $this->value ? 'true' : 'false';
  }
}
