<?php

namespace Cthulhu\ir\nodes;

class OrderedVariantDeclNode extends VariantDeclNode {
  public array $members;

  /**
   * @param Name   $name
   * @param Note[] $members
   */
  public function __construct(Name $name, array $members) {
    parent::__construct($name);
    $this->members = $members;
  }

  public function children(): array {
    return array_merge(
      [ $this->name ],
      $this->members
    );
  }
}
