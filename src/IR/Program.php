<?php

namespace Cthulhu\IR;

class Program {
  public $root_module;
  public $entry_point;

  function __construct(SourceModule $root_module, Symbol $entry_point) {
    $this->root_module = $root_module;
    $this->entry_point = $entry_point;
  }
}
