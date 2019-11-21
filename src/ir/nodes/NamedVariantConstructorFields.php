<?php

namespace Cthulhu\ir\nodes;

class NamedVariantConstructorFields extends VariantConstructorFields {
  public $pairs;

  /**
   * @param FieldExprNode[] $pairs
   */
  function __construct(array $pairs) {
    parent::__construct();
    $this->pairs = $pairs;
  }

  function children(): array {
    return $this->pairs;
  }
}
