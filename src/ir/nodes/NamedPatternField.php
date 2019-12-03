<?php

namespace Cthulhu\ir\nodes;

class NamedPatternField extends Node {
  public Name $name;
  public Pattern $pattern;

  function __construct(Name $name, Pattern $pattern) {
    parent::__construct();
    $this->name    = $name;
    $this->pattern = $pattern;
  }

  function children(): array {
    return [
      $this->name,
      $this->pattern,
    ];
  }
}
