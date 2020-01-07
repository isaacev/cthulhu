<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

abstract class VariantDeclNode extends Node {
  public UpperNameNode $name;

  public function __construct(Span $span, UpperNameNode $name) {
    parent::__construct($span);
    $this->name = $name;
  }
}
