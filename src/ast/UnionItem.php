<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UnionItem extends Item {
  public UpperNameNode $name;
  public array $variants;

  /**
   * @param Source\Span $span
   * @param UpperNameNode $name
   * @param VariantDeclNode[] $variants
   * @param Attribute[] $attrs
   */
  function __construct(Source\Span $span, UpperNameNode $name, array $variants, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name     = $name;
    $this->variants = $variants;
  }
}
