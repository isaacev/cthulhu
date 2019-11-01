<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

abstract class VariantConstructor extends Expr {
  public $path;

  function __construct(Source\Span $span, PathNode $path) {
    parent::__construct($span);
    $this->path = $path;
  }
}
