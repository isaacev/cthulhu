<?php

namespace Cthulhu\IR;

class SourceModule2 {
  public $builtins;
  public $scope;
  public $items;

  function __construct(array $builtins, ModuleScope3 $scope, array $items) {
    $this->builtins = $builtins;
    $this->scope = $scope;
    $this->items = $items;
  }

  public function scope(): ModuleScope3 {
    return $this->scope;
  }
}
