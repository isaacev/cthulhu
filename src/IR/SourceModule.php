<?php

namespace Cthulhu\IR;

use Cthulhu\Source;

class SourceModule {
  public $file;
  public $builtins;
  public $scope;
  public $items;

  function __construct(Source\File $file, array $builtins, ModuleScope $scope, array $items) {
    $this->file = $file;
    $this->builtins = $builtins;
    $this->scope = $scope;
    $this->items = $items;
  }

  public function scope(): ModuleScope {
    return $this->scope;
  }
}
