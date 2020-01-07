<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

abstract class Item extends Node {
  public array $attrs;

  /**
   * @param Span        $span
   * @param Attribute[] $attrs
   */
  public function __construct(Span $span, array $attrs) {
    parent::__construct($span);
    $this->attrs = $attrs;
  }
}
