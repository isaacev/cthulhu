<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ModItem extends Item {
  public UpperNameNode $name;
  public array $items;

  /**
   * @param Source\Span $span
   * @param UpperNameNode $name
   * @param Item[] $items
   * @param Attribute[] $attrs
   */
  function __construct(Source\Span $span, UpperNameNode $name, array $items, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->items = $items;
  }
}
