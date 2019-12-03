<?php

namespace Cthulhu\php\names;

class Symbol implements \Cthulhu\ir\HasId {
  use \Cthulhu\ir\GenerateId;

  function equals(Symbol $other): bool {
    return $this->get_id() === $other->get_id();
  }

  function __toString(): string {
    return (string)$this->get_id();
  }
}
