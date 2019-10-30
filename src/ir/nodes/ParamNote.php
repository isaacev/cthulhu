<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\nodes;

class ParamNote extends Note {
  public $name;

  function __construct(nodes\Name $name) {
    parent::__construct();
    $this->name = $name;
  }

  function children(): array {
    return [ $this->name ];
  }
}
