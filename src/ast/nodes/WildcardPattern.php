<?php

namespace Cthulhu\ast\nodes;

class WildcardPattern extends Pattern {
  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return "_";
  }
}
