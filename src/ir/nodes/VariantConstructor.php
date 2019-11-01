<?php

namespace Cthulhu\ir\nodes;

abstract class VariantConstructor extends Expr {
  public $ref;

  function __construct(Ref $ref) {
    parent::__construct();
    $this->ref = $ref;
  }
}
