<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UnnamedVariantConstructor extends VariantConstructor {
  public $members;

  /**
   * UnnamedVariantConstructor constructor.
   * @param Source\Span $span
   * @param PathNode $path
   * @param Expr[] $members
   */
  function __construct(Source\Span $span, PathNode $path, array $members) {
    parent::__construct($span, $path);
    $this->members = $members;
  }
}
