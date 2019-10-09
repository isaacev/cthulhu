<?php

namespace Cthulhu\IR;

class Program {
  public $root_module;
  public $libraries;
  public $entry_point;

  function __construct(SourceModule $root_module, array $libraries, Symbol $entry_point) {
    $this->root_module = $root_module;
    $this->libraries = $libraries;
    $this->entry_point = $entry_point;
  }
}
