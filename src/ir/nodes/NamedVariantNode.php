<?php

namespace Cthulhu\ir\nodes;

class NamedVariantNode extends VariantNode {
  public $fields;

  /**
   * NamedVariantNode constructor.
   * @param Name $name
   * @param FieldDeclNode[] $fields
   */
  function __construct(Name $name, array $fields) {
    parent::__construct($name);
    $this->fields = $fields;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->fields
    );
  }
}
