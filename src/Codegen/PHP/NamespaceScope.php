<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR;

class NamespaceScope extends Scope {
  public $parent;

  function __construct(?NamespaceScope $parent) {
    $this->parent = $parent;
  }
}
