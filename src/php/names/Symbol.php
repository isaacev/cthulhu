<?php

namespace Cthulhu\php\names;

use Cthulhu\ir;
use Cthulhu\lib\trees\DefaultUniqueId;

class Symbol implements ir\HasId {
  use DefaultUniqueId;

  public function equals(Symbol $other): bool {
    return $this->get_id() === $other->get_id();
  }

  public function __toString(): string {
    return (string)$this->get_id();
  }
}
