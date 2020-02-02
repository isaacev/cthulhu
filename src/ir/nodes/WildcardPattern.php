<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableNodelike;

class WildcardPattern extends Pattern {
  public function children(): array {
    return [];
  }

  public function from_children(array $children): EditableNodelike {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('_');
  }

  public function __toString(): string {
    return '_';
  }
}
