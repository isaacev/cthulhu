<?php

namespace Cthulhu\ir\patterns;

class WildcardPattern extends Pattern {
  public function __toString(): string {
    return '_';
  }
}
