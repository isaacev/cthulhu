<?php

namespace Cthulhu\IR;

class SourceModule {
  public $builtins;
  public $scope;
  public $items;

  function __construct(array $builtins, ModuleScope $scope, array $items) {
    $this->builtins = $builtins;
    $this->scope = $scope;
    $this->items = $items;
  }

  public function scope(): ModuleScope {
    return $this->scope;
  }
}
