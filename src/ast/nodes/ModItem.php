<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class ModItem extends Item {
  public UpperNameNode $name;
  public array $items;

  /**
   * @param Span          $span
   * @param UpperNameNode $name
   * @param Item[]        $items
   * @param Attribute[]   $attrs
   */
  public function __construct(Span $span, UpperNameNode $name, array $items, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name  = $name;
    $this->items = $items;
  }
}
