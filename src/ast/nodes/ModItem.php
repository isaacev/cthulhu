<?php

namespace Cthulhu\ast\nodes;

class ModItem extends Item {
  public UpperName $name;
  public array $items;

  /**
   * @param UpperName $name
   * @param Item[]    $items
   */
  public function __construct(UpperName $name, array $items) {
    parent::__construct();
    $this->name  = $name;
    $this->items = $items;
  }

  public function children(): array {
    return array_merge([ $this->name ], $this->items);
  }
}
