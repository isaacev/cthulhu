<?php

namespace Cthulhu\ir\names;

use Cthulhu\ir;

abstract class Symbol implements ir\HasId {
  use ir\GenerateId;

  function equals(Symbol $other): bool {
    return $this->get_id() === $other->get_id();
  }

  abstract function __toString(): string;
}
