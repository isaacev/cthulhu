<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class OrderedVariantDeclNode extends VariantDeclNode {
  public array $members;

  /**
   * @param Span          $span
   * @param UpperNameNode $name
   * @param Annotation[]  $members
   */
  public function __construct(Span $span, UpperNameNode $name, array $members) {
    parent::__construct($span, $name);
    $this->members = $members;
  }
}
