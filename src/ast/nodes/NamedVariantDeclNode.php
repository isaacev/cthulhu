<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NamedVariantDeclNode extends VariantDeclNode {
  public array $fields;

  /**
   * @param Span            $span
   * @param UpperName       $name
   * @param FieldDeclNode[] $fields
   */
  public function __construct(Span $span, UpperName $name, array $fields) {
    parent::__construct($span, $name);
    $this->fields = $fields;
  }
}
