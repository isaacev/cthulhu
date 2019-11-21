<?php

namespace Cthulhu\ir\nodes;

class WildcardPattern extends Pattern {
  public function children(): array {
    return [];
  }

  function __toString(): string {
    return '_';
  }
}
