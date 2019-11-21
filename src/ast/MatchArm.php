<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class MatchArm extends Node {
  public $pattern;
  public $handler;

  function __construct(Source\Span $span, Pattern $pattern, Expr $handler) {
    parent::__construct($span);
    $this->pattern = $pattern;
    $this->handler = $handler;
  }
}
