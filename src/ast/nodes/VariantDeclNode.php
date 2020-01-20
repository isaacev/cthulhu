<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

abstract class VariantDeclNode extends Node {
  public UpperName $name;

  public function __construct(Span $span, UpperName $name) {
    parent::__construct($span);
    $this->name = $name;
  }
}
