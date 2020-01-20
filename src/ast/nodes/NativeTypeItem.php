<?php

namespace Cthulhu\ast\nodes;

class NativeTypeItem extends Item {
  public UpperName $name;

  public function __construct(UpperName $name) {
    parent::__construct();
    $this->name = $name;
  }

  public function children(): array {
    return [ $this->name ];
  }
}
