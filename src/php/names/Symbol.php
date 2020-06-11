<?php

namespace Cthulhu\php\names;

use Cthulhu\lib\trees\DefaultUniqueId;

class Symbol {
  use DefaultUniqueId;

  public function __toString(): string {
    return (string)$this->get_id();
  }
}
