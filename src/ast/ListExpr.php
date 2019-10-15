<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ListExpr extends Expr {
  public $elements;

  function __construct(Source\Span $span, array $elements) {
    parent::__construct($span);
    $this->elements = $elements;
  }
}
