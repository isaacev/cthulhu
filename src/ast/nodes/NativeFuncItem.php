<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NativeFuncItem extends Item {
  public LowerNameNode $name;
  public FunctionAnnotation $note;

  /**
   * @param Span               $span
   * @param LowerNameNode      $name
   * @param FunctionAnnotation $note
   * @param Attribute[]        $attrs
   */
  public function __construct(Span $span, LowerNameNode $name, FunctionAnnotation $note, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
  }
}
