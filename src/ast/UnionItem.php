<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UnionItem extends Item {
  public $name;
  public $variants;

  /**
   * UnionItem constructor.
   * @param Source\Span $span
   * @param UpperNameNode $name
   * @param VariantDeclNode[] $variants
   * @param array $attrs
   */
  function __construct(Source\Span $span, UpperNameNode $name, array $variants, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name     = $name;
    $this->variants = $variants;
  }
}
