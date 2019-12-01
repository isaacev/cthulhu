<?php

namespace Cthulhu\ir\nodes;

class UseItem extends Item {
  public CompoundRef $ref;

  function __construct(CompoundRef $ref, array $attrs) {
    parent::__construct($attrs);
    $this->ref = $ref;
  }

  function children(): array {
    return [ $this->ref ];
  }
}
