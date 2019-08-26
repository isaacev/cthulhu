<?php

namespace Cthulhu\IR;

class SourceModule implements Module {
  public $scope;
  public $block;

  function __construct(ModuleScope $scope, BlockNode $block) {
    $this->scope = $scope;
    $this->block = $block;
  }

  public function scope(): ModuleScope {
    return $this->scope;
  }

  public function jsonSerialize() {
    return [
      'module' => 'source',
      'scope' => $this->scope->jsonSerialize(),
      'block' => $this->block->jsonSerialize()
    ];
  }
}
