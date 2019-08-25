<?php

namespace Cthulhu\IR;

class SourceModule implements Module {
  public $module_scope;
  public $block;

  function __construct(ModuleScope $module_scope, BlockNode $block) {
    $this->module_scope = $module_scope;
    $this->block = $block;
  }

  public function scope(): ModuleScope {
    return $this->module_scope;
  }

  public function jsonSerialize() {
    return [
      'module' => 'source',
      'block' => $this->block
    ];
  }
}
