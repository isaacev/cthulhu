<?php

namespace Cthulhu\ir\nodes;

class NamedVariantPatternFields extends VariantPatternFields {
  public $mapping;

  /**
   * OrderedVariantPatternFields constructor.
   * @param NamedPatternField[] $mapping
   */
  function __construct(array $mapping) {
    parent::__construct();
    $this->mapping = $mapping;
  }

  function children(): array {
    return $this->mapping;
  }
}
