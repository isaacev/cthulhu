<?php

namespace Cthulhu\ast\nodes;

class Attribute extends Node {
  public LowerName $name;
  public array $args;

  /**
   * @param LowerName   $name
   * @param LowerName[] $args
   */
  public function __construct(LowerName $name, array $args) {
    parent::__construct();
    $this->name = $name;
    $this->args = $args;
  }

  public function children(): array {
    return array_merge([ $this->name ], $this->args);
  }
}
