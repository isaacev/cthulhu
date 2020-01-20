<?php

namespace Cthulhu\ast\nodes;

class ShallowUseItem extends ShallowItem {
  public CompoundPathNode $path;

  public function __construct(CompoundPathNode $path) {
    parent::__construct();
    $this->path = $path;
  }

  public function children(): array {
    return [ $this->path ];
  }
}
