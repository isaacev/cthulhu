<?php

namespace Cthulhu\ir\types\hm;

use Exception;

class TypeMismatch extends Exception {
  public Type $left;
  public Type $right;

  public function __construct(Type $left, Type $right) {
    parent::__construct("type mismatch between $left and $right");
    $this->left  = $left;
    $this->right = $right;
  }
}
