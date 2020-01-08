<?php

namespace Cthulhu\ir\nodes;

class NamedVariantDeclNode extends VariantDeclNode {
  public array $fields;

  /**
   * @param Name            $name
   * @param FieldDeclNode[] $fields
   */
  public function __construct(Name $name, array $fields) {
    parent::__construct($name);
    $this->fields = $fields;
  }

  public function children(): array {
    return array_merge(
      [ $this->name ],
      $this->fields
    );
  }
}
