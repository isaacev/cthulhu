<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

abstract class VariantDeclNode extends Node {
  public $name;

  function __construct(Source\Span $span, UpperNameNode $name) {
    parent::__construct($span);
    $this->name = $name;
  }
}
