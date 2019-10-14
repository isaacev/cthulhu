<?php

namespace Cthulhu\ir\names;

class TypeSymbol extends Symbol {
  public function __toString(): string {
    return "<$this->id>";
  }
}
