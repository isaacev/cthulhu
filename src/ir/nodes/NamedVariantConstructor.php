<?php

namespace Cthulhu\ir\nodes;

class NamedVariantConstructor extends VariantConstructor {
  public $fields;

  /**
   * NamedVariantConstructor constructor.
   * @param Ref $ref
   * @param FieldExprNode[] $fields
   */
  function __construct(Ref $ref, array $fields) {
    parent::__construct($ref);
    $this->fields = $fields;
  }

  function children(): array {
    return array_merge(
      [ $this->ref ],
      $this->fields
    );
  }
}
