<?php

namespace Cthulhu\ast\nodes;

class TypeParamNote extends Note {
  public string $name;

  public function __construct(string $name) {
    parent::__construct();
    $this->name = $name;
  }

  public function children(): array {
    return [];
  }
}
