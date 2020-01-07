<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class Attribute extends Node {
  public string $name;

  public function __construct(Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }
}
