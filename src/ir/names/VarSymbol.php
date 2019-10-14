<?php

namespace Cthulhu\ir\names;

class VarSymbol extends Symbol {
  public function __toString(): string {
    return "($this->id)";
  }
}
