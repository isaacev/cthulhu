<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NativeFuncItem extends Item {
  public LowerNameNode $name;
  public FunctionAnnotation $note;

  /**
   * @param Source\Span $span
   * @param LowerNameNode $name
   * @param FunctionAnnotation $note
   * @param Attribute[] $attrs
   */
  function __construct(Source\Span $span, LowerNameNode $name, FunctionAnnotation $note, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name  = $name;
    $this->note  = $note;
  }
}
