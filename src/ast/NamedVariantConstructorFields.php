<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NamedVariantConstructorFields extends VariantConstructorFields {
  public array $pairs;

  /**
   * @param Source\Span $span
   * @param FieldExprNode[] $pairs
   */
  function __construct(Source\Span $span, array $pairs) {
    parent::__construct($span);
    $this->pairs = $pairs;
  }
}
