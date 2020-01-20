<?php

namespace Cthulhu\ast\nodes;

class NamedVariantConstructorFields extends VariantConstructorFields {
  public array $pairs;

  /**
   * @param FieldExprNode[] $pairs
   */
  public function __construct(array $pairs) {
    parent::__construct();
    $this->pairs = $pairs;
  }

  public function children(): array {
    return $this->pairs;
  }
}
