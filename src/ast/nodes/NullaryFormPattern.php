<?php

namespace Cthulhu\ast\nodes;

class NullaryFormPattern extends FormPattern {
  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return $this->path->tail->get('symbol')->__toString();
  }
}
