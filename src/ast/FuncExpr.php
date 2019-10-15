<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FuncExpr extends Expr {
  public $params;
  public $return_annotation;
  public $block;

  function __construct(Source\Span $span, array $params, Annotation $return_annotation, BlockNode $block) {
    parent::__construct($span);
    $this->params = $params;
    $this->return_annotation = $return_annotation;
    $this->block = $block;
  }
}
