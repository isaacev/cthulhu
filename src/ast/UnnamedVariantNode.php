<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UnnamedVariantNode extends VariantNode {
  public $members;

  /**
   * UnnamedVariantNode constructor.
   * @param Source\Span $span
   * @param UpperNameNode $name
   * @param Annotation[] $members
   */
  function __construct(Source\Span $span, UpperNameNode $name, array $members) {
    parent::__construct($span, $name);
    $this->members = $members;
  }
}
