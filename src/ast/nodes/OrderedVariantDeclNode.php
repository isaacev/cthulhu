<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class OrderedVariantDeclNode extends VariantDeclNode {
  public array $members;

  /**
   * @param Span      $span
   * @param UpperName $name
   * @param Note[]    $members
   */
  public function __construct(Span $span, UpperName $name, array $members) {
    parent::__construct($span, $name);
    $this->members = $members;
  }
}
