<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class LowerNameNode extends Node {
  public string $ident;

  public function __construct(Span $span, string $value) {
    assert($value[0] >= 'a' && $value[0] <= 'z');
    parent::__construct($span);
    $this->ident = $value;
  }
}
