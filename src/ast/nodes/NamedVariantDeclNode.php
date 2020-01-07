<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NamedVariantDeclNode extends VariantDeclNode {
  public array $fields;

  /**
   * @param Span            $span
   * @param UpperNameNode   $name
   * @param FieldDeclNode[] $fields
   */
  public function __construct(Span $span, UpperNameNode $name, array $fields) {
    parent::__construct($span, $name);
    $this->fields = $fields;
  }
}
