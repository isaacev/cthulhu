<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ModItem extends Item {
  public $name;
  public $items;

  function __construct(Source\Span $span, IdentNode $name, array $items, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->items = $items;
  }
}
