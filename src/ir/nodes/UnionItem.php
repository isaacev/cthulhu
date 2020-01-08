<?php

namespace Cthulhu\ir\nodes;

/**
 * @package Cthulhu\ir\nodes
 * @property-read Name              $name
 * @property-read ParamNote[]       $params
 * @property-read VariantDeclNode[] $variants
 */
class UnionItem extends Item {
  public Name $name;
  public array $params;
  public array $variants;

  /**
   * @param array             $attrs
   * @param Name              $name
   * @param ParamNote[]       $params
   * @param VariantDeclNode[] $variants
   */
  public function __construct(array $attrs, Name $name, array $params, array $variants) {
    parent::__construct($attrs);
    $this->name     = $name;
    $this->params   = $params;
    $this->variants = $variants;
  }

  public function children(): array {
    return array_merge(
      [ $this->name ],
      $this->params,
      $this->variants
    );
  }
}
