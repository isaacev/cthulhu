<?php

namespace Cthulhu\ir\nodes;

class NamedRefSegment extends RefSegment {
  public $name;

  function __construct(Name $name) {
    parent::__construct();
    $this->name = $name;
  }

  function children(): array {
    return [ $this->name ];
  }
}
