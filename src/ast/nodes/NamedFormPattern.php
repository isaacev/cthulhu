<?php

namespace Cthulhu\ast\nodes;

class NamedFormPattern extends FormPattern {
  public array $pairs;

  /**
   * @param PathNode          $path
   * @param NamePatternPair[] $pairs
   */
  public function __construct(PathNode $path, array $pairs) {
    parent::__construct($path);
    $this->pairs = $pairs;
  }

  public function children(): array {
    return array_merge([ $this->path ], $this->pairs);
  }

  public function fields_to_string(): string {
    return "(" . implode(", ", $this->pairs) . ")";
  }
}
