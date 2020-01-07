<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class FnItem extends Item {
  public LowerNameNode $name;
  public array $params;
  public Annotation $returns;
  public BlockNode $body;

  /**
   * @param Span          $span
   * @param LowerNameNode $name
   * @param ParamNode[]   $params
   * @param Annotation    $returns
   * @param BlockNode     $body
   * @param Attribute[]   $attrs
   */
  public function __construct(Span $span, LowerNameNode $name, array $params, Annotation $returns, BlockNode $body, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name    = $name;
    $this->params  = $params;
    $this->returns = $returns;
    $this->body    = $body;
  }
}
