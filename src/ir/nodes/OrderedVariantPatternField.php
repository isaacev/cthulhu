<?php

namespace Cthulhu\ir\nodes;

class OrderedVariantPatternField extends Node {
  public int $position;
  public Pattern $pattern;

  function __construct(int $position, Pattern $pattern) {
    parent::__construct();
    $this->position = $position;
    $this->pattern = $pattern;
  }

  public function children(): array {
    return [ $this->pattern ];
  }

  public function __toString(): string {
    return "$this->pattern";
  }
}
