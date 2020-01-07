<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class FieldExprNode extends Node {
  public LowerNameNode $name;
  public Expr $expr;

  public function __construct(Span $span, LowerNameNode $name, Expr $expr) {
    parent::__construct($span);
    $this->name = $name;
    $this->expr = $expr;
  }
}
