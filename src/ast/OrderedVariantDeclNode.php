<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class OrderedVariantDeclNode extends VariantDeclNode {
  public $members;

  /**
   * @param Source\Span $span
   * @param UpperNameNode $name
   * @param Annotation[] $members
   */
  function __construct(Source\Span $span, UpperNameNode $name, array $members) {
    parent::__construct($span, $name);
    $this->members = $members;
  }
}
