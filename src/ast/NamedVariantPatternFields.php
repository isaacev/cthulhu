<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NamedVariantPatternFields extends VariantPatternFields {
  public array $mapping;

  /**
   * @param Source\Span         $span
   * @param NamedPatternField[] $mapping
   */
  function __construct(Source\Span $span, array $mapping) {
    parent::__construct($span);
    $this->mapping = $mapping;
  }
}
