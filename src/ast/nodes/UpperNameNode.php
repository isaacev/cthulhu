<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class UpperNameNode extends Node {
  public string $ident;

  public function __construct(Span $span, string $value) {
    assert($value[0] >= 'A' && $value[0] <= 'Z');
    parent::__construct($span);
    $this->ident = $value;
  }
}
