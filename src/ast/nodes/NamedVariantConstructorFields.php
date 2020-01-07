<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NamedVariantConstructorFields extends VariantConstructorFields {
  public array $pairs;

  /**
   * @param Span            $span
   * @param FieldExprNode[] $pairs
   */
  public function __construct(Span $span, array $pairs) {
    parent::__construct($span);
    $this->pairs = $pairs;
  }
}
