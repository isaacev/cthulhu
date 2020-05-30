<?php

namespace Cthulhu\ast\nodes;

class OrderedFormPattern extends FormPattern {
  public array $order;

  /**
   * @param PathNode  $path
   * @param Pattern[] $pairs
   */
  public function __construct(PathNode $path, array $pairs) {
    parent::__construct($path);
    $this->order = $pairs;
  }

  public function children(): array {
    return array_merge([ $this->path ], $this->order);
  }

  public function fields_to_string(): string {
    return "(" . implode(", ", $this->order) . ")";
  }
}
