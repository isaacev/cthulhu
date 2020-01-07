<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NamedVariantPatternFields extends VariantPatternFields {
  public array $mapping;

  /**
   * @param Span                $span
   * @param NamedPatternField[] $mapping
   */
  public function __construct(Span $span, array $mapping) {
    parent::__construct($span);
    $this->mapping = $mapping;
  }
}
