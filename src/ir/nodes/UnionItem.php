<?php

namespace Cthulhu\ir\nodes;

class UnionItem extends Item {
  public $name;
  public $variants;

  /**
   * UnionItem constructor.
   * @param array $attrs
   * @param Name $name
   * @param VariantNode[] $variants
   */
  function __construct(array $attrs, Name $name, array $variants) {
    parent::__construct($attrs);
    $this->name     = $name;
    $this->variants = $variants;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->variants
    );
  }
}
