<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class PathNode extends Node {
  public $extern;
  public $segments;

  function __construct(Source\Span $span, bool $extern, array $segments) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->segments = $segments;
  }
}
