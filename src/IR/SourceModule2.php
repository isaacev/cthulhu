<?php

namespace Cthulhu\IR;

class SourceModule2 {
  public $scope;
  public $items;

  function __construct(ModuleScope3 $scope, array $items) {
    $this->scope = $scope;
    $this->items = $items;
  }

  public function scope(): ModuleScope {
    return $this->scope;
  }

  public function jsonSerialize() {
    return [
      'items' => $this->items
    ];
  }
}
