<?php

namespace Cthulhu\ir\patterns;

class IntPattern extends Pattern {
  public int $value;

  function __construct(int $value) {
    $this->value = $value;
  }

  function __toString(): string {
    return (string)$this->value;
  }
}
