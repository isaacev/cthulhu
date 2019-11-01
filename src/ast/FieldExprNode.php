<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FieldExprNode extends Node {
  public $name;
  public $expr;

  function __construct(Source\Span $span, LowerNameNode $name, Expr $expr) {
    parent::__construct($span);
    $this->name = $name;
    $this->expr = $expr;
  }
}
