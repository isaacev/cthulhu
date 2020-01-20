<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NativeFuncItem extends Item {
  public LowerName $name;
  public FuncNote $note;

  /**
   * @param Span        $span
   * @param LowerName   $name
   * @param FuncNote    $note
   * @param Attribute[] $attrs
   */
  public function __construct(Span $span, LowerName $name, FuncNote $note, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
  }
}
