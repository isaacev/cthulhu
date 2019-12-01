<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FnItem extends Item {
  public LowerNameNode $name;
  public array $params;
  public Annotation $returns;
  public BlockNode $body;

  /**
   * @param Source\Span $span
   * @param LowerNameNode $name
   * @param ParamNode[] $params
   * @param Annotation $returns
   * @param BlockNode $body
   * @param Attribute[] $attrs
   */
  function __construct(Source\Span $span, LowerNameNode $name, array $params, Annotation $returns, BlockNode $body, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->params = $params;
    $this->returns = $returns;
    $this->body = $body;
  }
}
