<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class NamedPatternField extends Node {
  public LowerName $name;
  public Pattern $pattern;

  public function __construct(Span $span, LowerName $name, Pattern $pattern) {
    parent::__construct($span);
    $this->name    = $name;
    $this->pattern = $pattern;
  }
}
