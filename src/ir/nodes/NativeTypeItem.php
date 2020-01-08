<?php

namespace Cthulhu\ir\nodes;

class NativeTypeItem extends Item {
  public Name $name;

  public function __construct(Name $name, array $attrs) {
    parent::__construct($attrs);
    $this->name = $name;
  }

  public function children(): array {
    return [ $this->name ];
  }
}
