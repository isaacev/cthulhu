<?php

namespace Cthulhu\IR;

class FunctionScope extends Scope {
  public $parent;
  public $generics;
  public $signature;

  function __construct(ModuleScope $parent, array $generics, Types\FunctionType $signature) {
    $this->parent = $parent;
    $this->generics = $generics;
    $this->signature = $signature;
  }
}
