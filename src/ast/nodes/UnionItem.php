<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class UnionItem extends Item {
  public UpperNameNode $name;
  public array $params;
  public array $variants;

  /**
   * @param Span                  $span
   * @param UpperNameNode         $name
   * @param TypeParamAnnotation[] $params
   * @param VariantDeclNode[]     $variants
   * @param Attribute[]           $attrs
   */
  public function __construct(Span $span, UpperNameNode $name, array $params, array $variants, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name     = $name;
    $this->params   = $params;
    $this->variants = $variants;
  }
}
