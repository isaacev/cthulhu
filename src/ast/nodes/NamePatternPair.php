<?php

namespace Cthulhu\ast\nodes;

class NamePatternPair extends Node {
  public LowerName $name;
  public Pattern $pattern;

  public function __construct(LowerName $name, Pattern $pattern) {
    parent::__construct();
    $this->name    = $name;
    $this->pattern = $pattern;
  }

  public function children(): array {
    return [ $this->name, $this->pattern ];
  }

  public function __toString() {
    return "$this->name:$this->pattern";
  }
}
