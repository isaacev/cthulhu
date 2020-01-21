<?php

namespace Cthulhu\ir\names;

class Binding {
  public string $name;
  public Symbol $symbol;
  public bool $is_public;

  public function __construct(string $name, Symbol $symbol, bool $is_public) {
    $this->name      = $name;
    $this->symbol    = $symbol;
    $this->is_public = $is_public;
  }

  public function as_private(): self {
    return new self($this->name, $this->symbol, false);
  }
}
