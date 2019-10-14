<?php

namespace Cthulhu\ir\nodes;

class NativeTypeItem extends Item {
  public $name;

  function __construct(Name $name, array $attrs) {
    parent::__construct($attrs);
    $this->name = $name;
  }

  function children(): array {
    return [ $this->name ];
  }
}
