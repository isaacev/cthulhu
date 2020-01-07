<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NativeTypeItem extends Item {
  public UpperNameNode $name;

  /**
   * @param Span          $span
   * @param UpperNameNode $name
   * @param Attribute[]   $attrs
   */
  public function __construct(Span $span, UpperNameNode $name, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
  }
}
