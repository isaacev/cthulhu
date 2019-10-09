<?php

namespace Cthulhu\IR;

class ModItem extends Item {
  public $scope;
  public $items;

  function __construct(ModuleScope $scope, array $items, array $attrs) {
    parent::__construct($attrs);
    $this->scope = $scope;
    $this->items = $items;
  }
}
