<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NamedVariantConstructor extends VariantConstructor {
  public $fields;

  /**
   * NamedVariantConstructor constructor.
   * @param Source\Span $span
   * @param PathNode $path
   * @param FieldExprNode[] $fields
   */
  function __construct(Source\Span $span, PathNode $path, array $fields) {
    parent::__construct($span, $path);
    $this->fields = $fields;
  }
}
