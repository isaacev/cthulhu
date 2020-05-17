<?php

namespace Cthulhu\ir\names;

class TermBinding extends Binding {
  public function __construct(string $name, Symbol $symbol, bool $is_public) {
    parent::__construct($name, $symbol, $is_public);
  }

  public function as_private(): self {
    return new TermBinding($this->name, $this->symbol, false);
  }
}
