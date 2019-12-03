<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NamedVariantDeclNode extends VariantDeclNode {
  public array $fields;

  /**
   * @param Source\Span     $span
   * @param UpperNameNode   $name
   * @param FieldDeclNode[] $fields
   */
  function __construct(Source\Span $span, UpperNameNode $name, array $fields) {
    parent::__construct($span, $name);
    $this->fields = $fields;
  }
}
