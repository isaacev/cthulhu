<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class File extends Node {
  public array $items;

  /**
   * @param Span   $span
   * @param Item[] $items
   */
  public function __construct(Span $span, array $items) {
    parent::__construct($span);
    $this->items = $items;
  }
}
