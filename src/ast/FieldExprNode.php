<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FieldExprNode extends Node {
  public LowerNameNode $name;
  public Expr $expr;

  function __construct(Source\Span $span, LowerNameNode $name, Expr $expr) {
    parent::__construct($span);
    $this->name = $name;
    $this->expr = $expr;
  }
}
