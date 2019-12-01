<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

abstract class Stmt extends Node {
  public array $attrs;

  /**
   * @param Source\Span $span
   * @param Attribute[] $attrs
   */
  function __construct(Source\Span $span, array $attrs) {
    parent::__construct($span);
    $this->attrs = $attrs;
  }
}
