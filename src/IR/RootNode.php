<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class RootNode extends Node {
  public $scope;
  public $block;

  function __construct(GlobalScope $scope, BlockNode $block) {
    $this->scope = $scope;
    $this->block = $block;
  }

  public function type(): Types\Type {
    return $this->block->type();
  }

  public function jsonSerialize() {
    return [
      'type' => 'RootNode',
      'block' => $this->block->jsonSerialize()
    ];
  }
}
