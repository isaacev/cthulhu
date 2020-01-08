<?php

namespace Cthulhu\ir\nodes;

class ParamNote extends Note {
  public Name $name;

  public function __construct(Name $name) {
    parent::__construct();
    $this->name = $name;
  }

  public function children(): array {
    return [ $this->name ];
  }
}
