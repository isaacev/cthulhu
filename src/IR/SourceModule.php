<?php

namespace Cthulhu\IR;

use Cthulhu\Source;

class SourceModule implements Module {
  public $file;
  public $scope;
  public $items;

  function __construct(Source\File $file, ModuleScope $scope, array $items) {
    $this->file = $file;
    $this->scope = $scope;
    $this->items = $items;
  }

  public function scope(): ModuleScope {
    return $this->scope;
  }
}
