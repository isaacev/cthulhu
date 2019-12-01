<?php

namespace Cthulhu\ir\nodes;

class ParamNote extends Note {
  public Name $name;

  function __construct(Name $name) {
    parent::__construct();
    $this->name = $name;
  }

  function children(): array {
    return [ $this->name ];
  }
}
