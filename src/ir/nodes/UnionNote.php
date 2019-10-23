<?php

namespace Cthulhu\ir\nodes;

class UnionNote extends Note {
  public $members;

  function __construct(array $members) {
    parent::__construct();
    $this->members = $members;
  }

  function children(): array {
    return $this->members;
  }
}
