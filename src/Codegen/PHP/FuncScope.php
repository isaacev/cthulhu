<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR;

class FuncScope extends Scope {
  public $parent_scope;

  function __construct(Scope $parent_scope) {
    $this->parent_scope = $parent_scope;
  }
}
