<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\ast\TokenGroup;

class ShallowBlock extends ShallowNode {
  public TokenGroup $group;

  public function __construct(TokenGroup $group) {
    parent::__construct();
    $this->group = $group;
  }

  public function children(): array {
    return [];
  }
}
