<?php

namespace Cthulhu\ir\nodes;

class NamedVariantPatternFields extends VariantPatternFields {
  public array $mapping;

  /**
   * @param NamedPatternField[] $mapping
   */
  public function __construct(array $mapping) {
    parent::__construct();
    $this->mapping = $mapping;
  }

  public function children(): array {
    return $this->mapping;
  }
}
