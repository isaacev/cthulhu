<?php

namespace Cthulhu\ir\nodes;

class UnionItem extends Item {
  public Name $name;
  public array $params;
  public array $variants;

  /**
   * @param array             $attrs
   * @param Name              $name
   * @param ParamNote[]
   * @param VariantDeclNode[] $variants
   */
  function __construct(array $attrs, Name $name, array $params, array $variants) {
    parent::__construct($attrs);
    $this->name     = $name;
    $this->params   = $params;
    $this->variants = $variants;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->params,
      $this->variants
    );
  }
}
