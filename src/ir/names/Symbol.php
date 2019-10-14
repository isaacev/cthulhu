<?php

namespace Cthulhu\ir\names;

abstract class Symbol implements \Cthulhu\ir\HasId {
  use \Cthulhu\ir\GenerateId;

  function equals(Symbol $other): bool {
    return $this->get_id() === $other->get_id();
  }

  abstract function __toString(): string;
}
