<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class VariantConstructorExpr extends Expr {
  public $path;
  public $fields;

  function __construct(Source\Span $span, PathNode $path, ?VariantConstructorFields $fields) {
    parent::__construct($span);
    $this->path = $path;
    $this->fields = $fields;
  }
}
