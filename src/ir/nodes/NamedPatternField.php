<?php

namespace Cthulhu\ir\nodes;

class NamedPatternField extends Node {
  public $name;
  public $pattern;

  function __construct(Name $name, Pattern $pattern) {
    parent::__construct();
    $this->name = $name;
    $this->pattern = $pattern;
  }

  function children(): array {
    return [
      $this->name,
      $this->pattern,
    ];
  }
}
