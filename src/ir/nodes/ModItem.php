<?php

namespace Cthulhu\ir\nodes;

class ModItem extends Item {
  public Name $name;
  public array $items;

  /**
   * @param Name   $name
   * @param Item[] $items
   * @param array  $attrs
   */
  public function __construct(Name $name, array $items, array $attrs) {
    parent::__construct($attrs);
    $this->name  = $name;
    $this->items = $items;
  }

  public function children(): array {
    return array_merge(
      [ $this->name ],
      $this->items
    );
  }
}
