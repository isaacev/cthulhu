<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class UseItem extends Item {
  public CompoundPathNode $path;

  /**
   * @param Span             $span
   * @param CompoundPathNode $path
   * @param Attribute[]      $attrs
   */
  public function __construct(Span $span, CompoundPathNode $path, array $attrs) {
    parent::__construct($span, $attrs);
    $this->path = $path;
  }
}
