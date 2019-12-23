<?php

namespace Cthulhu\ir\types\traits;

use Cthulhu\ir\types\Type;

trait NoChildren {
  /**
   * @return Type[]
   */
  function to_children(): array {
    return [];
  }

  /**
   * @param Type[] $children
   * @return $this
   */
  function from_children(array $children): self {
    return $this;
  }
}
