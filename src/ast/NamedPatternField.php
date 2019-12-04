<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NamedPatternField extends Node {
  public LowerNameNode $name;
  public Pattern $pattern;

  function __construct(Source\Span $span, LowerNameNode $name, Pattern $pattern) {
    parent::__construct($span);
    $this->name    = $name;
    $this->pattern = $pattern;
  }
}