<?php

namespace Cthulhu\ir\nodes;

class NamedPatternField extends Node {
  public Name $name;
  public Pattern $pattern;

  public function __construct(Name $name, Pattern $pattern) {
    parent::__construct();
    $this->name    = $name;
    $this->pattern = $pattern;
  }

  public function children(): array {
    return [
      $this->name,
      $this->pattern,
    ];
  }
}
