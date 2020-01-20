<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc;

class ShallowFile extends ShallowNode {
  public loc\File $file;
  public UpperName $name;
  public array $items;

  /**
   * @param loc\File      $file
   * @param ShallowItem[] $items
   */
  public function __construct(loc\File $file, array $items) {
    parent::__construct();
    $this->file  = $file;
    $this->name  = (new UpperName($file->basename()))
      ->set('span', (new loc\Point($file, 1, 1))->span());
    $this->items = $items;
  }

  public function children(): array {
    return array_merge([ $this->name, ], $this->items);
  }

  public function __toString(): string {
    return $this->file->filepath;
  }
}
