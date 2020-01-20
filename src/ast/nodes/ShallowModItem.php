<?php

namespace Cthulhu\ast\nodes;

class ShallowModItem extends ShallowItem {
  public UpperName $name;
  public array $items;

  /**
   * @param UpperName     $name
   * @param ShallowItem[] $items
   */
  public function __construct(UpperName $name, array $items) {
    parent::__construct();
    $this->name  = $name;
    $this->items = $items;
  }

  public function children(): array {
    return [ $this->name, ...$this->items ];
  }
}
