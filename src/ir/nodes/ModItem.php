<?php

namespace Cthulhu\ir\nodes;

class ModItem extends Item {
  public $name;
  public $items;

  function __construct(Name $name, array $items, array $attrs) {
    parent::__construct($attrs);
    $this->name  = $name;
    $this->items = $items;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->items
    );
  }
}
